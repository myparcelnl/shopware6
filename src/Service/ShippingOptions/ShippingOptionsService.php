<?php


namespace Kiener\KienerMyParcel\Service\ShippingOptions;


use Kiener\KienerMyParcel\Core\Content\Shipment\ShipmentEntity;
use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ShippingOptionsService
{
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

        return $this->shippingOptionsRepository->search($criteria, $context)->get($id);
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
}