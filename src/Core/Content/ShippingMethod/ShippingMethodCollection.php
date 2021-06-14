<?php declare(strict_types=1);

namespace MyPaShopware\Core\Content\ShippingMethod;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ShippingMethodCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ShippingMethodEntity::class;
    }
}
