<?php /** @noinspection PhpUndefinedClassInspection */

namespace MyPa\Shopware\Service\Consignment;

use Composer\InstalledVersions;
use Exception;
use MyPa\Shopware\Core\Content\Shipment\ShipmentEntity;
use MyPa\Shopware\Exception\Config\ConfigFieldValueMissingException;
use MyPa\Shopware\Helper\AddressHelper;
use MyPa\Shopware\Service\Order\OrderService;
use MyPa\Shopware\Service\Shipment\InsuranceService;
use MyPa\Shopware\Service\Shipment\ShipmentService;
use MyPa\Shopware\Service\ShippingOptions\ShippingOptionsService;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use MyParcelNL\Sdk\src\Model\MyParcelCustomsItem;
use MyParcelNL\Sdk\src\Support\Str;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Document\DocumentCollection;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\DocumentGenerator\InvoiceGenerator;
use Shopware\Core\Checkout\Document\Renderer\InvoiceRenderer;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConsignmentService
{
    private const FIELD_ORDER_ID = 'order_id';
    private const FIELD_ORDER_VERSION_ID = 'order_version_id';
    private const FIELD_SHIPPING_OPTION_ID = 'shipping_option_id';
    private const FIELD_PACKAGE_TYPE = 'package_type';

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var ShippingOptionsService
     */
    private $shippingOptionsService;

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * @var string
     */
    private $apiKey;

    /** @var SystemConfigService */
    private $systemConfigService;

    /** @var InsuranceService */
    private $insuranceService;

    private $shopwareVersion;

    /** @var LoggerInterface */
    private $logger;

    /**
     * ConsignmentService constructor.
     *
     * @param OrderService $orderService
     * @param ShippingOptionsService $shippingOptionsService
     * @param ShipmentService $shipmentService
     * @param SystemConfigService $systemConfigService
     * @param InsuranceService $insuranceService
     * @param $shopwareVersion
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderService           $orderService,
        ShippingOptionsService $shippingOptionsService,
        ShipmentService        $shipmentService,
        SystemConfigService    $systemConfigService,
        InsuranceService       $insuranceService,
                               $shopwareVersion,
        LoggerInterface        $logger
    )
    {
        $this->orderService = $orderService;
        $this->shippingOptionsService = $shippingOptionsService;
        $this->shipmentService = $shipmentService;
        $this->systemConfigService = $systemConfigService;
        $this->apiKey = (string)$systemConfigService->get('MyPaShopware.config.myParcelApiKey');
        $this->insuranceService = $insuranceService;
        $this->shopwareVersion = $shopwareVersion;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getCarrierIds(): array
    {
        return [
            BpostConsignment::CARRIER_NAME => BpostConsignment::CARRIER_ID,
            DPDConsignment::CARRIER_NAME => DPDConsignment::CARRIER_ID,
            PostNLConsignment::CARRIER_NAME => PostNLConsignment::CARRIER_ID,
        ];
    }

    /**
     * @return array
     */
    public function getPackageTypes(): array
    {
        return [
            AbstractConsignment::PACKAGE_TYPE_PACKAGE => AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME,
            AbstractConsignment::PACKAGE_TYPE_MAILBOX => AbstractConsignment::PACKAGE_TYPE_MAILBOX_NAME,
            AbstractConsignment::PACKAGE_TYPE_LETTER => AbstractConsignment::PACKAGE_TYPE_LETTER_NAME,
            AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP => AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        ];
    }

    private function createLabelDescription(OrderEntity $order, string $shippingDate): string {
        $description  = $this->systemConfigService->get('MyPaShopware.config.labelDescription') ?? '';
        $quantities   = [];
        $productSkus  = [];
        $productNames = [];
        $productIds   = [];

        if (Str::contains($description, '[PRODUCT')) {
            $orderLineItems = $order->getLineItems();
            foreach ($orderLineItems as $orderLineItem) {
                $quantities[]   = $orderLineItem->getQuantity();
                $productSkus[]  = $orderLineItem->getPayload()['productNumber'];
                $productNames[] = $orderLineItem->getLabel();
                $productIds[]   = $orderLineItem->getProductId();
            }

        }

        $formattedDescription = strtr($description, [
            '[ORDER_NR]'      => $order->getOrderNumber(),
            '[CUSTOMER_NOTE]' => $order->getCustomerComment(),
            '[DELIVERY_DATE]' => $shippingDate,
            '[PRODUCT_QTY]'   => implode(', ', $quantities),
            '[PRODUCT_SKU]'   => implode(', ', $productSkus),
            '[PRODUCT_NAME]'  => implode(', ', $productNames),
            '[PRODUCT_ID]'    => implode(', ', $productIds),
        ]);

        return Str::limit($formattedDescription, AbstractConsignment::LABEL_DESCRIPTION_MAX_LENGTH);
    }

    /**
     * @param  Context     $context
     * @param  OrderEntity $orderEntity
     * @param  null|int    $packageType
     *
     * @return AbstractConsignment
     * @throws MissingFieldException
     * @throws \Exception
     */
    public function createConsignment(
        Context     $context,
        OrderEntity $orderEntity,
        ?int        $packageType
    ): AbstractConsignment
    {
        if (null === $orderEntity->getOrderCustomer()) {
            throw new RuntimeException('Could not get a customer');
        }
        if (
            null === $orderEntity->getDeliveries()
            || null === $orderEntity->getDeliveries()->first()
            || null === $orderEntity->getDeliveries()->first()->getShippingOrderAddress()
        ) {
            throw new \RuntimeException('Could not get a shipping address');
        }

        $shippingAddress = $orderEntity->getDeliveries()->first()->getShippingOrderAddress();

        if (null === $shippingAddress
            || null === $shippingAddress->getCountry()
        ) {
            throw new \RuntimeException('Shipping address is not properly formatted');
        }

        $parsedAddress = AddressHelper::parseAddress($shippingAddress, $this->systemConfigService->get('MyPaShopware.config'));

        $shippingOptions = $this->shippingOptionsService->getShippingOptionsForOrder($orderEntity, $context);

        if (null === $shippingOptions) {
            throw new \RuntimeException('No valid Shipping Options found');
        }

        $consignment = (ConsignmentFactory::createByCarrierId($shippingOptions->getCarrierId()))
            ->setApiKey($this->apiKey)
            ->setReferenceIdentifier($orderEntity->getOrderNumber() . '-' . Uuid::randomHex())
            ->setCountry($shippingAddress->getCountry()->getIso())
            ->setCompany($shippingAddress->getCompany())
            ->setPerson(
                sprintf('%s %s', $shippingAddress->getFirstName(), $shippingAddress->getLastName())
            )
            ->setFullStreet(
                sprintf('%s %s %s', $parsedAddress['street'], $parsedAddress['houseNumber'], $parsedAddress['houseNumberAddition'])
            )
            ->setPostalCode(trim($shippingAddress->getZipcode()))
            ->setCity($shippingAddress->getCity())
            ->setEmail($orderEntity->getOrderCustomer()->getEmail());

        //Set invoice number to the latest invoice document number or order number if none is available
        $documents = $orderEntity->getDocuments() ?? new DocumentCollection();
        $invoice = $documents->filter(function ($document) {
            /** @var DocumentEntity $document */
            return $document->getDocumentType() && InvoiceRenderer::TYPE === $document->getDocumentType()->getTechnicalName();
        })->last();

        if ($invoice instanceof DocumentEntity) {
            $invoiceNumber = $invoice->getConfig()['documentNumber'];
        } else {
            $invoiceNumber = $orderEntity->getOrderNumber();
        }
        $consignment->setInvoice($invoiceNumber);

        if (null !== $shippingOptions->getDeliveryDate()) {

            $shippingDate = $shippingOptions->getDeliveryDate()->format('Y-m-d');

            if (strtotime($shippingDate) <= strtotime("today")) {
                $shippingDate = \date("Y-m-d", \strtotime('tomorrow'));
            }
            $consignment->setDeliveryDate($shippingDate);
        }

        $consignment->setLabelDescription($this->createLabelDescription($orderEntity, $shippingDate));

        $isRow = !in_array($shippingAddress->getCountry()->getIso(),AbstractConsignment::EURO_COUNTRIES);
        $totalWeight = 0;
        /** @var OrderLineItemEntity $lineItem */
        foreach ($orderEntity->getLineItems() as $lineItem) {
            if ($isRow) {
                $customsItem = new MyParcelCustomsItem();
                if ($lineItem->getProduct()
                    && $lineItem->getProduct()
                        ->getWeight()) {
                    $customsItem->setWeight(
                        $lineItem->getProduct()
                            ->getWeight() * 1000
                    );
                } else {
                    $customsItem->setWeight(0.01);
                }
                $customsItem->setAmount($lineItem->getQuantity());
                $customsItem->setDescription($lineItem->getLabel());
                $customsItem->setItemValue($lineItem->getUnitPrice() * 100);// In cents
                if ('myparcel' === $this->systemConfigService->getString('MyPaShopware.config.platform')) {
                    $customsItem->setCountry('NL');
                } else {
                    $customsItem->setCountry('BE');
                }
                //Get custom field HS code
                $customFields = $lineItem->getPayload()['customFields'];
                $hsCode       = $this->systemConfigService->getString('MyPaShopware.config.myParcelFallbackHSCode');

                if ($customFields && array_key_exists('myparcel_product_hs_code', $customFields)) {
                    $hsCode = $customFields['myparcel_product_hs_code'];
                }
                if (empty($hsCode)) {
                    throw new ConfigFieldValueMissingException();
                }

                $customsItem->setClassification((int) $hsCode);

                $consignment->addItem($customsItem);

                $totalWeight += $customsItem->getWeight() * $customsItem->getAmount();
            } elseif ($lineItem->getProduct()) {
                $totalWeight += $lineItem->getQuantity() * $lineItem->getProduct()->getWeight() * 1000;
            }
        }

        $consignment->setTotalWeight(min($totalWeight, 30000));

        if (
            $shippingOptions->getDeliveryDate() !== null
            && is_int($shippingOptions->getDeliveryType())
            && in_array($shippingOptions->getDeliveryType(), AbstractConsignment::DELIVERY_TYPES_IDS, true)
        ) {
            $consignment->setDeliveryType($shippingOptions->getDeliveryType());
        }

        if (
            is_int($shippingOptions->getDeliveryType())
            && in_array($shippingOptions->getDeliveryType(), AbstractConsignment::DELIVERY_TYPES_IDS, true)
        ) {
            $consignment->setDeliveryType($shippingOptions->getDeliveryType());
        }

        if (
            is_int($shippingOptions->getPackageType())
            && in_array($shippingOptions->getPackageType(), AbstractConsignment::PACKAGE_TYPES_IDS, true)
        ) {
            $consignment->setPackageType($shippingOptions->getPackageType());
        } else if ($packageType) {
            $consignment->setPackageType($packageType);
        }

        if ($shippingOptions->getRequiresAgeCheck() !== null) {
            if ($consignment instanceof DPDConsignment) {
                $consignment->setAgeCheck(false);
            } else {
                $consignment->setAgeCheck($shippingOptions->getRequiresAgeCheck());
            }
        }

        if ($shippingOptions->getLargeFormat() !== null) {
            if ($consignment instanceof DPDConsignment) {
                $consignment->setLargeFormat(false);
            } else {
                $consignment->setLargeFormat($shippingOptions->getLargeFormat());
            }
        }

        if ($shippingOptions->getRequiresSignature() !== null) {
            if ($consignment instanceof DPDConsignment) {
                $consignment->setSignature(false);
            } else {
                $consignment->setSignature($shippingOptions->getRequiresSignature());
            }
        }

        if ($shippingOptions->getOnlyRecipient() !== null) {
            if ($consignment instanceof DPDConsignment) {
                $consignment->setOnlyRecipient(false);
            } else {
                $consignment->setOnlyRecipient($shippingOptions->getOnlyRecipient());
            }
        }

        if ($shippingOptions->getReturnIfNotHome() !== null) {
            $consignment->setReturn($shippingOptions->getReturnIfNotHome());
        }

        if ($shippingOptions->getDeliveryType() == AbstractConsignment::DELIVERY_TYPE_PICKUP) {
            $consignment->setPickupLocationCode((string) $shippingOptions->getLocationId());
            $consignment->setPickupLocationName($shippingOptions->getLocationName());
            $consignment->setPickupStreet($shippingOptions->getLocationStreet());
            $consignment->setPickupNumber($shippingOptions->getLocationNumber());
            $consignment->setPickupPostalCode($shippingOptions->getLocationPostalCode());
            $consignment->setPickupCity($shippingOptions->getLocationCity());
            $consignment->setPickupCountry($shippingOptions->getLocationCc());
            $consignment->setRetailNetworkId($shippingOptions->getRetailNetworkId());
        }

        $insuranceAmount = $this->insuranceService->getInsuranceAmount(
            $orderEntity->getAmountNet(),
            $shippingAddress->getCountry(),
            $shippingOptions->getCarrierId(),
            $context
        );

        if ($insuranceAmount) {
            $consignment->setInsurance($insuranceAmount);
        }

        return $consignment;
    }

    /**
     * @param Context $context
     * @param OrderEntity $orderEntity
     * @param string $shippingOptionId
     * @param AbstractConsignment $consignment
     *
     * @return ShipmentEntity|null
     */
    private function createShipment(
        Context             $context,
        OrderEntity         $orderEntity,
        string              $shippingOptionId,
        AbstractConsignment $consignment
    ): ?ShipmentEntity
    {
        $shipmentParameters = [
            ShipmentEntity::FIELD_CONSIGNMENT_REFERENCE => $consignment->getReferenceIdentifier(),
            ShipmentEntity::FIELD_ORDER => [
                ShipmentEntity::FIELD_ID => $orderEntity->getId(),
                ShipmentEntity::FIELD_VERSION_ID => $orderEntity->getVersionId(),
            ],
            ShipmentEntity::FIELD_SHIPPING_OPTION => [
                ShipmentEntity::FIELD_ID => $shippingOptionId,
            ],
        ];

        if (null !== $consignment->getBarcode()) {
            $shipmentParameters[ShipmentEntity::FIELD_BAR_CODE] = $consignment->getBarcode();
            $shipmentParameters[ShipmentEntity::FIELD_TRACK_AND_TRACE_URL] = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                trim($consignment->getPostalCode()),
                $consignment->getCountry()
            );

            // Add track and trace to the custom fields
            $customFields = json_decode($orderEntity->getCustomFields()['my_parcel'], true);
            $trackAndTrace = $customFields['track_and_trace'] ?? [];

            $trackAndTrace[] = [
                'bar_code' => $shipmentParameters[ShipmentEntity::FIELD_BAR_CODE],
                'url' => $shipmentParameters[ShipmentEntity::FIELD_TRACK_AND_TRACE_URL],
            ];

            $customFields['track_and_trace'] = $trackAndTrace;

            $this->orderService->createOrUpdateOrder([
                'id' => $orderEntity->getId(),
                'versionId' => $orderEntity->getVersionId(),
                'customFields' => $customFields,
            ], $context);
        }

        return $this->shipmentService->createOrUpdateShipment($shipmentParameters, $context);
    }

    /**
     * @param Context $context
     * @param ShipmentEntity $shipment
     * @param string $labelUrl
     *
     * @return ShipmentEntity|null
     */
    private function addLabelUrlToShipment(Context $context, ShipmentEntity $shipment, string $labelUrl): ?ShipmentEntity
    {
        $shipmentParameters = [
            ShipmentEntity::FIELD_ID => $shipment->getId(),
            ShipmentEntity::FIELD_LABEL_URL => $labelUrl,
        ];

        return $this->shipmentService->createOrUpdateShipment($shipmentParameters, $context);
    }

    /**
     * @param Context $context
     * @param array $ordersData
     *
     * @param array|null $labelPositions
     * @param int|null $packageType
     * @param int|null $numberOfLabels
     *
     * @return MyParcelCollection
     * @throws MissingFieldException
     * @throws Exception
     */
    public function createConsignments( //NOSONAR
        Context $context,
        array   $ordersData,
        ?array  $labelPositions,
        ?int    $packageType,
        ?int    $numberOfLabels
    ): MyParcelCollection //NOSONAR
    {
        $consignments = (new MyParcelCollection());
        $shipmentData = [];
        $shipments = [];

        $rootPackage = InstalledVersions::getRootPackage();

        $consignments->setUserAgents([
            'MyParcel-Shopware' => $rootPackage['pretty_version'],
            'Shopware'          => $this->shopwareVersion]
        );

        /** @var OrderEntity $order */
        foreach ($ordersData as $orderData) {

            if (
                !array_key_exists(self::FIELD_ORDER_ID, $orderData)
                || !array_key_exists(self::FIELD_ORDER_VERSION_ID, $orderData)
                || !array_key_exists(self::FIELD_SHIPPING_OPTION_ID, $orderData)
            ) {
                continue;
            }

            /** @var OrderEntity $order */
            $order = $this->getFullOrderEntity($orderData[self::FIELD_ORDER_ID], $orderData[self::FIELD_ORDER_VERSION_ID], $context);

            if (null !== $order) {
                if (!$numberOfLabels) {
                    $numberOfLabels = 1;
                }

                for ($i = 1; $i <= $numberOfLabels; $i++) {
                    try {
                        $consignment = $this->createConsignment($context, $order, $packageType);
                    } catch(\Throwable $e) {
                        $message = "{$order->getOrderNumber()}: could not create consignment";
                        $this->logger->warning($message, ['error'=>$e->getMessage()]);

                        continue;
                    }

                    $consignments->addConsignment($consignment);

                    $shipmentData[] = [
                        'context' => $context,
                        'order' => $order,
                        'shippingOptionId' => $orderData[self::FIELD_SHIPPING_OPTION_ID],
                        'referenceId' => $consignment->getReferenceIdentifier(),
                    ];
                }
            }
        }

        if ($consignments->isNotEmpty()) {
            if (
                isset($labelPositions)
                && !empty($labelPositions)
            ) {
                $consignments->setLinkOfLabels(count($labelPositions) === 1 ? $labelPositions[0] : $labelPositions);
            } else {
                $consignments->setLinkOfLabels(false);
            }
        }

        if (
            is_array($shipmentData)
            && !empty($shipmentData)
        ) {
            foreach ($shipmentData as $shipment) {
                $consignment = null;
                $foundConsignments = $this->findByReferenceId($shipment['referenceId']);

                if (
                    is_array($foundConsignments)
                    && !empty($foundConsignments)
                ) {
                    $consignment = $foundConsignments[0];
                }

                if (null !== $consignment) {
                    $this->createShipment($shipment['context'], $shipment['order'], $shipment['shippingOptionId'], $consignment);
                }
            }
        }

        return $consignments;
    }

    /**
     * @param string $referenceId
     *
     * @return array
     * @throws MissingFieldException
     */
    public function findByReferenceId(string $referenceId): array
    {
        $consignments = MyParcelCollection::findByReferenceId($referenceId, $this->apiKey);

        return $consignments->toArray();
    }

    /**
     * @param array $referenceIds
     *
     * @return MyParcelCollection
     * @throws MissingFieldException
     */
    public function findManyByReferenceId(array $referenceIds): MyParcelCollection
    {
        return MyParcelCollection::findManyByReferenceId($referenceIds, $this->apiKey);
    }

    /**
     * @param  string $shippingOptionId
     *
     * @return OrderEntity|null
     */
    public function getFullOrderByShippingOptionId(string $shippingOptionId): ?OrderEntity
    {
        $options = $this->shippingOptionsService->getShippingOptions(
            $shippingOptionId,
            new Context(new SystemSource())
        );

        if (! $options) {
            return null;
        }

        $order = $options->getOrder();

        return $this->getFullOrderEntity($order->getId(), $order->getVersionId(), new Context(new SystemSource()));
    }

    /**
     * @param  string  $orderId
     * @param  string  $orderVersionId
     * @param  Context $context
     *
     * @return null|OrderEntity
     */
    private function getFullOrderEntity(string $orderId, string $orderVersionId, Context $context): ?OrderEntity
    {
        return $this->orderService->getOrder($orderId, $orderVersionId, $context, [
            'addresses',
            'deliveries',
            'deliveries.shippingOrderAddress',
            'deliveries.shippingOrderAddress.country',
            'lineItems',
            'lineItems.product.customFields',
            'documents.documentType'
        ]);
    }
}
