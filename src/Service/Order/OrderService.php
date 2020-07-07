<?php

namespace Kiener\KienerMyParcel\Service\Shipment;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class OrderService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * Creates a new instance of the shipment service
     *
     * @param EntityRepositoryInterface $orderRepository
     */
    public function __construct(
        LoggerInterface $logger,
        EntityRepositoryInterface $orderRepository
    )
    {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Creates or updates an order in the database.
     *
     * @param array   $params
     * @param Context $context
     *
     * @return OrderEntity|null
     */
    public function createOrUpdateOrder(array $params, Context $context): ?OrderEntity
    {
        // Create a new order entity if no id is present
        if (!isset($params['id'])) {
            $params['id'] = Uuid::randomHex();
        }

        // Upsert the data in the database
        $event = $this->orderRepository->upsert([$params], $context);

        // Check for errors
        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(', ', $event->getErrors()),
                $event->getErrors()
            );

            return null;
        }

        return $this->getOrder($params['id'], $context);
    }

    /**
     * Returns a order object from the database.
     *
     * @param string  $id
     * @param Context $context
     *
     * @return OrderEntity|null
     */
    public function getOrder(string $id, Context $context): ?OrderEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('currency');
        $criteria->addAssociation('addresses');
        $criteria->addAssociation('language');
        $criteria->addAssociation('language.locale');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('deliveries');
        $criteria->addAssociation('deliveries.shippingOrderAddress');

        return $this->orderRepository->search($criteria, $context)->get($id);
    }
}