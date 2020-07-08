<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel\Service\ShippingMethod;

use Kiener\KienerMyParcel\Core\Content\ShippingMethod\ShippingMethodEntity;
use Kiener\KienerMyParcel\Service\Consignment\ConsignmentService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Rule\CartAmountRule;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity as ShopwareShippingMethodEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;

class ShippingMethodService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsignmentService
     */
    private $consignmentService;

    /**
     * @var EntityRepositoryInterface
     */
    private $myParcelShippingMethodRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $shopwareShippingMethodRepository;

    public function __construct(
        LoggerInterface $logger,
        ConsignmentService $consignmentService,
        EntityRepositoryInterface $myParcelShippingMethodRepository,
        EntityRepositoryInterface $shopwareShippingMethodRepository
    )
    {
        $this->logger = $logger;
        $this->consignmentService = $consignmentService;
        $this->myParcelShippingMethodRepository = $myParcelShippingMethodRepository;
        $this->shopwareShippingMethodRepository = $shopwareShippingMethodRepository;
    }

    public function createShippingMethods(Context $context): void
    {
        /** @var array $carriers */
        $carriers = $this->consignmentService->getCarrierIds();

        if (
            is_array($carriers)
            && !empty($carriers)
        ) {
            foreach ($carriers as $carrierName => $carrierId) {
                // Create a Shopware shipping method
                $shippingMethodId = $this->createShopwareShippingMethod(
                    $carrierId,
                    $carrierName,
                    $context
                );

                // Connect the shipping method to a MyParcel carrier
                if ($shippingMethodId !== null) {
                    $this->createMyParcelShippingMethod(
                        $shippingMethodId,
                        $carrierId,
                        $carrierName,
                        $context
                    );

                    // @todo Handle errors, so the merchant knows if any issues occur
                }
            }
        }
    }

    /**
     * Returns whether a shipping method is a MyParcel shipping method.
     *
     * @param ShopwareShippingMethodEntity $shippingMethod
     * @param Context                      $context
     *
     * @return bool
     */
    public function isMyParcelShippingMethod(ShopwareShippingMethodEntity $shippingMethod, Context $context): bool
    {
        return $this->getShippingMethodByShopwareShippingMethodId($shippingMethod->getId(), $context) !== null;
    }

    /**
     * Returns a shipping method by carrier id.
     *
     * @param string  $shopwareShippingMethodId
     * @param Context $context
     *
     * @return ShippingMethodEntity|null
     */
    public function getShippingMethodByShopwareShippingMethodId(string $shopwareShippingMethodId, Context $context): ?ShippingMethodEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('shippingMethodId', $shopwareShippingMethodId));
        $criteria->addAssociation('kiener_my_parcel_shipping_method.shipping_method');

        $shippingMethods = $this->myParcelShippingMethodRepository->search($criteria, $context);

        return $shippingMethods->first();
    }

    /**
     * Returns a shipping method by carrier id.
     *
     * @param int     $carrierId
     * @param Context $context
     *
     * @return ShippingMethodEntity|null
     */
    public function getShippingMethodByCarrierId(int $carrierId, Context $context): ?ShippingMethodEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('carrierId', $carrierId));
        $criteria->addAssociation('kiener_my_parcel_shipping_method.shipping_method');

        $shippingMethods = $this->myParcelShippingMethodRepository->search($criteria, $context);

        return $shippingMethods->first();
    }

    /**
     * Creates a shipping method for MyParcel.
     *
     * @param string  $shippingMethodId
     * @param int     $carrierId
     * @param string  $carrierName
     * @param Context $context
     *
     * @return bool
     */
    private function createMyParcelShippingMethod(
        string $shippingMethodId,
        int $carrierId,
        string $carrierName,
        Context $context
    ): bool
    {
        /** @var string $id */
        $id = Uuid::randomHex();

        /** @var ShippingMethodEntity|null $existingShippingMethod */
        $existingShippingMethod = $this->getShippingMethodByCarrierId($carrierId, $context);

        if ($existingShippingMethod !== null) {
            $id = $existingShippingMethod->getId();
        }

        /** @var EntityWrittenEvent $event */
        $event = $this->myParcelShippingMethodRepository->upsert([
            [
                'id' => $id,
                'carrierId' => $carrierId,
                'carrierName' => $carrierName,
                'shippingMethod' => [
                    'id' => $shippingMethodId
                ],
            ]
        ], $context);

        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(',', $event->getErrors()),
                $event->getErrors()
            );

            return false;
        }

        return true;
    }

    /**
     * Creates a shipping method in Shopware.
     *
     * @param int     $carrierId
     * @param string  $carrierName
     * @param Context $context
     *
     * @return string|null
     */
    private function createShopwareShippingMethod(
        int $carrierId,
        string $carrierName,
        Context $context
    ): ?string
    {
        /** @var string $id */
        $id = Uuid::randomHex();

        /** @var ShippingMethodEntity|null $existingShippingMethod */
        $existingShippingMethod = $this->getShippingMethodByCarrierId($carrierId, $context);

        if ($existingShippingMethod !== null) {
            return $existingShippingMethod->getShippingMethodId();
        }

        // Create or update the shipping method
        $event = $this->shopwareShippingMethodRepository->upsert([
            [
                'id' => $id,
                'name' => $carrierName,
                'active' => false,
                'availabilityRule' => [
                    'name' => 'Cart >= 0',
                    'priority' => 1,
                    'type' => (new CartAmountRule())->getName(),
                ],
                'deliveryTime' => [
                    'min' => 1,
                    'max' => 3,
                    'unit' => DeliveryTimeEntity::DELIVERY_TIME_DAY,
                    'name' => '1 - 3 days',
                    'translations' => [
                        Defaults::LANGUAGE_SYSTEM => [
                            'name' => '1 - 3 days',
                        ],
                    ],
                ],
            ]
        ], $context);

        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(', ', $event->getErrors()),
                $event->getErrors()
            );

            return null;
        }

        return $id;
    }
}