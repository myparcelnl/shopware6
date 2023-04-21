<?php /** @noinspection PhpUndefinedClassInspection */

namespace MyPa\Shopware\Service\Consignment;

use Exception;
use MyPa\Shopware\Core\Content\Shipment\ShipmentEntity;
use MyPa\Shopware\Defaults;
use MyPa\Shopware\Exception\Config\ConfigFieldValueMissingException;
use MyPa\Shopware\Helper\AddressHelper;
use MyPa\Shopware\Service\Order\OrderService;
use MyPa\Shopware\Service\Shipment\InsuranceService;
use MyPa\Shopware\Service\Shipment\ShipmentService;
use MyPa\Shopware\Service\ShippingOptions\ShippingOptionsService;
use MyPa\Shopware\Struct\DropOffPointStruct;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use MyParcelNL\Sdk\src\Model\MyParcelCustomsItem;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\DocumentGenerator\InvoiceGenerator;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
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

    /**
     * @param Context $context
     * @param OrderEntity $orderEntity
     * @param null|int $packageType
     *
     * @return AbstractConsignment|null
     * @throws MissingFieldException
     * @throws Exception
     */
    private function createConsignment(
        Context     $context,
        OrderEntity $orderEntity,
        ?int        $packageType
    ): ?AbstractConsignment
    {
        if ($orderEntity->getOrderCustomer() === null) {
            throw new RuntimeException('Could not get a customer');
        }
        if (
            $orderEntity->getDeliveries() === null ||
            $orderEntity->getDeliveries()->first() === null ||
            $orderEntity->getDeliveries()->first()->getShippingOrderAddress() === null
        ) {
            throw new \RuntimeException('Could not get a shipping address');
        }

        $shippingAddress = $orderEntity->getDeliveries()->first()->getShippingOrderAddress();

        if ($shippingAddress === null ||
            $shippingAddress->getCountry() === null
        ) {
            throw new \RuntimeException('Shipping address is not properly formatted');
        }

        $parsedAddress = AddressHelper::parseAddress($shippingAddress, $this->systemConfigService->get('MyPaShopware.config'));

        $shippingOptions = $this->shippingOptionsService->getShippingOptionsForOrder($orderEntity, $context);

        if ($shippingOptions === null) {
            throw new \RuntimeException('No valid Shipping Options found');
        }

        $consignment = (ConsignmentFactory::createByCarrierId($shippingOptions->getCarrierId()))
            ->setApiKey($this->apiKey)
            ->setReferenceId($orderEntity->getOrderNumber() . '-' . Uuid::randomHex())
            ->setCountry($shippingAddress->getCountry()->getIso())
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
        $invoice = $orderEntity->getDocuments()->filter(function ($document) {
            /** @var DocumentEntity $document */
            return $document->getDocumentType()->getTechnicalName() === InvoiceGenerator::INVOICE;
        })->last();

        if ($invoice instanceof DocumentEntity) {
            $invoiceNumber = $invoice->getConfig()['documentNumber'];
        } else {
            $invoiceNumber = $orderEntity->getOrderNumber();
        }
        $consignment->setInvoice($invoiceNumber);


        if ($shippingOptions->getCarrierId() == Defaults::CARRIER_TO_ID['instabox']) {
            //Add drop off point if instabox
            $dropOffJson = $this->systemConfigService->getString('MyPaShopware.config.dropOffInstabox');
            if (!empty($dropOffJson)) {
                $dropOffStruct = new DropOffPointStruct();
                $dropOffStruct->assign(json_decode($dropOffJson, true));
                $consignment->setDropOffPoint($dropOffStruct->getDropOffPoint());
            }
            $this->logger->error('Instabox drop off location not set while trying to make an instabox consignment',
                [
                    'order' => $orderEntity,
                    'shippingOptions' => $shippingOptions
                ]);
        }

        if ($shippingOptions->getDeliveryDate() !== null) {

            $shippingDate = $shippingOptions->getDeliveryDate()->format('Y-m-d');

            if (strtotime($shippingDate) <= strtotime("today")) {
                $shippingDate = \date("Y-m-d", \strtotime('tomorrow'));
            }
            $consignment->setDeliveryDate($shippingDate);
        }

        // Not in europe
        if (!in_array($shippingAddress->getCountry()->getIso(),AbstractConsignment::EURO_COUNTRIES)) {
            //Add weight of all items for international shipping
            /** @var OrderLineItemEntity $lineItem */
            foreach ($orderEntity->getLineItems() as $lineItem) {

                $customsItem = new MyParcelCustomsItem();
                if ($lineItem->getProduct()->getWeight()) {
                    $customsItem->setWeight($lineItem->getProduct()->getWeight() * 1000);
                } else {
                    $customsItem->setWeight(0.01);
                }
                $customsItem->setAmount($lineItem->getQuantity());
                $customsItem->setDescription($lineItem->getLabel());
                $customsItem->setItemValue($lineItem->getUnitPrice() * 100);// In cents
                if ($this->systemConfigService->getString('MyPaShopware.config.platform') === "myparcel") {
                    $customsItem->setCountry('NL');
                } else {
                    $customsItem->setCountry('BE');
                }
                //Get custom field HS code
                $customFields = $lineItem->getPayload()['customFields'];
                $hsCode = $this->systemConfigService->getString('MyPaShopware.config.myParcelFallbackHSCode');

                if ($customFields && array_key_exists('myparcel_product_hs_code', $customFields)) {
                    $hsCode = $customFields['myparcel_product_hs_code'];
                }
                if (empty($hsCode)) {
                    throw new ConfigFieldValueMissingException();
                }

                $customsItem->setClassification(intval($hsCode));

                $consignment->addItem($customsItem);
            }
        }

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

        if ($consignment->getPackageType() == AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP) {

            $totalWeight = 0;
            $lineItems = $orderEntity->getLineItems();
            if ($lineItems) {
                foreach ($lineItems as $lineItem) {
                    $totalWeight += $lineItem->getProduct()->getWeight();
                }
                //Shopware uses KG for weight, MyParcel wants Grams
                $totalWeight = $totalWeight * 1000;
            }

            $consignment->setTotalWeight($totalWeight);
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
            $consignment->setPickupLocationCode(strval($shippingOptions->getLocationId()));
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
            ShipmentEntity::FIELD_CONSIGNMENT_REFERENCE => $consignment->getReferenceId(),
            ShipmentEntity::FIELD_ORDER => [
                ShipmentEntity::FIELD_ID => $orderEntity->getId(),
                ShipmentEntity::FIELD_VERSION_ID => $orderEntity->getVersionId(),
            ],
            ShipmentEntity::FIELD_SHIPPING_OPTION => [
                ShipmentEntity::FIELD_ID => $shippingOptionId,
            ],
        ];

        if ($consignment->getBarcode() !== null) {
            $shipmentParameters[ShipmentEntity::FIELD_BAR_CODE] = $consignment->getBarcode();
            $shipmentParameters[ShipmentEntity::FIELD_TRACK_AND_TRACE_URL] = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                trim($consignment->getPostalCode()),
                $consignment->getCountry()
            );

            // Add track and trace to the custom fields
            $customFields = json_decode($orderEntity->getCustomFields()['my_parcel'], true) ?? null;
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

        $consignments->setUserAgent('Shopware', $this->shopwareVersion);

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
            $order = $this->orderService->getOrder($orderData[self::FIELD_ORDER_ID], $orderData[self::FIELD_ORDER_VERSION_ID], $context, [
                'addresses',
                'deliveries',
                'deliveries.shippingOrderAddress',
                'deliveries.shippingOrderAddress.country',
                'lineItems',
                'lineItems.product.customFields',
                'documents.documentType'
            ]);

            if ($order !== null) {
                if (!$numberOfLabels || is_null($numberOfLabels)) {
                    $numberOfLabels = 1;
                }

                for ($i = 1; $i <= $numberOfLabels; $i++) {
                    $consignment = $this->createConsignment($context, $order, $packageType);

                    if ($consignment !== null) {
                        $consignments->addConsignment($consignment);
                    }

                    $shipmentData[] = [
                        'context' => $context,
                        'order' => $order,
                        'shippingOptionId' => $orderData[self::FIELD_SHIPPING_OPTION_ID],
                        'referenceId' => $consignment->getReferenceId(),
                    ];
                }
            }
        }

        if ($consignments->isEmpty() === false) {
            if (
                isset($labelPositions)
                && is_array($labelPositions)
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

                if ($consignment !== null) {
                    $createdShipment = $this->createShipment($shipment['context'], $shipment['order'], $shipment['shippingOptionId'], $consignment);
                    $shipments[] = $createdShipment;
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
}
