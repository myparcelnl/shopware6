<?php

namespace Kiener\KienerMyParcel\Service\ShippingOptions;

use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ShippingOptionsService
{
    private const FIELD_NAME = 'name';
    private const FIELD_COSTS = 'costs';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityRepositoryInterface
     */
    private $shippingOptionsRepository;

    /**
     * ShippingOptionsService constructor.
     *
     * @param LoggerInterface           $logger
     * @param EntityRepositoryInterface $shippingOptionsRepository
     */
    public function __construct(
        LoggerInterface $logger,
        EntityRepositoryInterface $shippingOptionsRepository
    )
    {
        $this->logger = $logger;
        $this->shippingOptionsRepository = $shippingOptionsRepository;
    }

    /**
     * @param array   $params
     * @param Context $context
     *
     * @return ShippingOptionEntity|null
     */
    public function createOrUpdateShippingOptions(array $params, Context $context): ?ShippingOptionEntity
    {
        // Create a new shipment entity if no id is present
        if (!isset($params[ShippingOptionEntity::FIELD_ID])) {
            $params[ShippingOptionEntity::FIELD_ID] = Uuid::randomHex();
        }

        // Upsert the data in the database
        $event = $this->shippingOptionsRepository->upsert([$params], $context);

        // Check for errors
        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(', ', $event->getErrors()),
                $event->getErrors()
            );

            return null;
        }

        return $this->getShippingOptions($params[ShippingOptionEntity::FIELD_ID], $context);
    }

    /**
     * @param string  $id
     * @param Context $context
     *
     * @return ShippingOptionEntity|null
     */
    public function getShippingOptions(string $id, Context $context): ?ShippingOptionEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('order');
        //$criteria->addAssociation('consignments');

        return $this->shippingOptionsRepository->search($criteria, $context)->get($id);
    }

    /**
     * @param string  $id
     * @param Context $context
     *
     * @return array
     */
    public function getAllShippingOptions(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('order');
        $criteria->addAssociation('consignments');

        return $this->shippingOptionsRepository->search($criteria, $context)->getVars();
    }

    /**
     * @param OrderEntity $orderEntity
     * @param Context     $context
     *
     * @return ShippingOptionEntity|null
     */
    public function getShippingOptionsForOrder(OrderEntity $orderEntity, Context $context): ?ShippingOptionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $orderEntity->getId()))
            ->addFilter(new EqualsFilter('orderVersionId', $orderEntity->getVersionId()));

        return $this->shippingOptionsRepository->search($criteria, $context)->first();
    }

    /**
     * @return array
     */
    public function getDeliveryTypes(): array
    {
        return [
            AbstractConsignment::DELIVERY_TYPE_MORNING => [
                self::FIELD_NAME => AbstractConsignment::DELIVERY_TYPE_MORNING_NAME,
                self::FIELD_COSTS => 0,
            ],
            AbstractConsignment::DELIVERY_TYPE_STANDARD => [
                self::FIELD_NAME => AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME,
                self::FIELD_COSTS => 0,
            ],
            AbstractConsignment::DELIVERY_TYPE_EVENING => [
                self::FIELD_NAME => AbstractConsignment::DELIVERY_TYPE_EVENING_NAME,
                self::FIELD_COSTS => 0,
            ],
            AbstractConsignment::DELIVERY_TYPE_PICKUP => [
                self::FIELD_NAME => AbstractConsignment::DELIVERY_TYPE_PICKUP_NAME,
                self::FIELD_COSTS => 0,
            ],
        ];
    }
}
