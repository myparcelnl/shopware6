<?php declare(strict_types=1);

namespace MyPa\Shopware\Core\Content\Shipment;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ShipmentCollection extends EntityCollection
{
    public function getExpectedClass(): string
    {
        return ShipmentEntity::class;
    }
}
