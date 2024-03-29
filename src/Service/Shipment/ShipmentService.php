<?php

namespace MyPa\Shopware\Service\Shipment;

use MyPa\Shopware\Core\Content\Shipment\ShipmentCollection;
use MyPa\Shopware\Core\Content\Shipment\ShipmentEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ShipmentService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityRepository
     */
    private $shipmentRepository;

    /**
     * Creates a new instance of the shipment service
     *
     * @param LoggerInterface  $logger
     * @param EntityRepository $shipmentRepository
     */
    public function __construct(
        LoggerInterface  $logger,
        EntityRepository $shipmentRepository
    )
    {
        $this->logger = $logger;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Creates or updates a shipment in the database.
     *
     * @param array   $params
     * @param Context $context
     *
     * @return ShipmentEntity|null
     */
    public function createOrUpdateShipment(array $params, Context $context): ?ShipmentEntity
    {
        // Create a new shipment entity if no id is present
        if (!isset($params[ShipmentEntity::FIELD_ID])) {
            $params[ShipmentEntity::FIELD_ID] = Uuid::randomHex();
        }

        // Upsert the data in the database
        $event = $this->shipmentRepository->upsert([$params], $context);

        // Check for errors
        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(', ', $event->getErrors()),
                $event->getErrors()
            );

            return null;
        }

        return $this->getShipment($params[ShipmentEntity::FIELD_ID], $context);
    }

    /**
     * Returns a shipment object from the database.
     *
     * @param string  $id
     * @param Context $context
     *
     * @return ShipmentEntity|null
     */
    public function getShipment(string $id, Context $context): ?ShipmentEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('shipping_option');
        $criteria->addAssociation('order');

        return $this->shipmentRepository->search($criteria, $context)->get($id);
    }

    /**
     * Returns shipment objects from the database by the shipping option id.
     *
     * @param string  $shippingOptionId
     * @param Context $context
     *
     * @return ShipmentCollection
     */
    public function getShipmentsByShippingOptionId(string $shippingOptionId, Context $context): ShipmentCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('shippingOptionId', $shippingOptionId));
        $criteria->addAssociation('shipping_option');
        $criteria->addAssociation('order');

        /** @var ShipmentCollection */
        return $this->shipmentRepository->search($criteria, $context)->getEntities();
    }

    /**
     * Returns a search result of shipments from the database.
     *
     * @param Context $context
     *
     * @return array
     */
    public function getShipments(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('shipping_option');
        $criteria->addAssociation('order');

        return $this->shipmentRepository->search($criteria, $context)->getElements();
    }
}
