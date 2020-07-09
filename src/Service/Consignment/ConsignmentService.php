<?php /** @noinspection PhpUndefinedClassInspection */

namespace Kiener\KienerMyParcel\Service\Consignment;

use Exception;
use Kiener\KienerMyParcel\Helper\AddressHelper;
use Kiener\KienerMyParcel\Service\Order\OrderService;
use Kiener\KienerMyParcel\Service\ShippingOptions\ShippingOptionsService;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use RuntimeException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConsignmentService
{
    private const FIELD_ORDER_ID = 'order_id';
    private const FIELD_ORDER_VERSION_ID = 'order_version_id';

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var ShippingOptionsService
     */
    private $shippingOptionsService;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * ConsignmentService constructor.
     *
     * @param OrderService           $orderService
     * @param ShippingOptionsService $shippingOptionsService
     * @param SystemConfigService    $systemConfigService
     */
    public function __construct(
        OrderService $orderService,
        ShippingOptionsService $shippingOptionsService,
        SystemConfigService $systemConfigService
    )
    {
        $this->orderService = $orderService;
        $this->shippingOptionsService = $shippingOptionsService;

        $this->apiKey = (string)$systemConfigService->get('KienerMyParcel.config.myParcelApiKey');
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
     * @param Context     $context
     * @param OrderEntity $orderEntity
     *
     * @return AbstractConsignment|null
     * @throws MissingFieldException
     */
    private function createConsignment(
        Context $context,
        OrderEntity $orderEntity
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
            throw new RuntimeException('Could not get a shipping address');
        }

        $shippingAddress = $orderEntity->getDeliveries()->first()->getShippingOrderAddress();

        if ($shippingAddress === null ||
            $shippingAddress->getCountry() === null
        ) {
            throw new RuntimeException('Shipping address is not properly formatted');
        }

        $parsedAddress = AddressHelper::parseAddress($shippingAddress);

        $shippingOptions = $this->shippingOptionsService->getShippingOptionsForOrder($orderEntity, $context);

        if ($shippingOptions === null) {
            throw new RuntimeException('No valid Shipping Options found');
        }

        $consignment = (ConsignmentFactory::createByCarrierId($shippingOptions->getCarrierId()))
            ->setApiKey($this->apiKey)
            ->setReferenceId($orderEntity->getId())
            ->setCountry($shippingAddress->getCountry()->getIso())
            ->setPerson(
                sprintf('%s %s', $shippingAddress->getFirstName(), $shippingAddress->getLastName())
            )
            ->setFullStreet(
                sprintf('%s %s %s', $parsedAddress['street'], $parsedAddress['houseNumber'], $parsedAddress['houseNumberAddition'])
            )
            ->setPostalCode($shippingAddress->getZipcode())
            ->setCity($shippingAddress->getCity())
            ->setEmail($orderEntity->getOrderCustomer()->getEmail());

        if (
            $shippingOptions->getPackageType() !== null
            && is_int($shippingOptions->getPackageType())
            && in_array($shippingOptions->getPackageType(), AbstractConsignment::PACKAGE_TYPES_IDS, true)
        ) {
            $consignment->setPackageType($shippingOptions->getPackageType());
        }

        if ($shippingOptions->getRequiresAgeCheck() !== null) {
            $consignment->setAgeCheck($shippingOptions->getRequiresAgeCheck());
        }

        if ($shippingOptions->getLargeFormat() !== null) {
            $consignment->setLargeFormat($shippingOptions->getLargeFormat());
        }

        if ($shippingOptions->getRequiresSignature() !== null) {
            $consignment->setSignature($shippingOptions->getRequiresSignature());
        }

        if ($shippingOptions->getOnlyRecipient() !== null) {
            $consignment->setOnlyRecipient($shippingOptions->getOnlyRecipient());
        }

        try {
            if ($shippingOptions->getReturnIfNotHome() !== null) {
                $consignment->setReturn($shippingOptions->getReturnIfNotHome());
            }

            return $consignment;
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        return null;
    }

    /**
     * @param Context    $context
     * @param array      $orderIds
     *
     * @param array|null $labelPositions
     *
     * @return MyParcelCollection
     * @throws MissingFieldException
     */
    public function createConsignments(Context $context, array $orderIds, ?array $labelPositions): MyParcelCollection //NOSONAR
    {
        $consignments = (new MyParcelCollection());

        /** @var OrderEntity $order */
        foreach ($orderIds as $orderId) {

            if(
                !array_key_exists(self::FIELD_ORDER_ID, $orderId)
                || !array_key_exists(self::FIELD_ORDER_VERSION_ID, $orderId)
            )
            {
                continue;
            }

            /** @var OrderEntity $order */
            $order = $this->orderService->getOrder($orderId[self::FIELD_ORDER_ID], $orderId[self::FIELD_ORDER_VERSION_ID],$context, [
                'addresses',
                'deliveries',
                'deliveries.shippingOrderAddress',
                'deliveries.shippingOrderAddress.country',
            ]);

            if ($order !== null) {
                $consignment = $this->createConsignment($context, $order);

                if ($consignment !== null) {
                    $consignments->addConsignment($consignment);
                }
            }
        }

        try {
            if (is_array($labelPositions) && !empty($labelPositions)) {
                if (count($labelPositions) === 1) {

                    $consignments->setPdfOfLabels($labelPositions[0]);
                } else {
                    $consignments->setPdfOfLabels($labelPositions);
                }
            } else {
                $consignments->setPdfOfLabels(false);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
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
}