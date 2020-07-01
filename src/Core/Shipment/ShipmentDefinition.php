<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel\Core\Shipment;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ShipmentDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'kn_my_parcel_shipments';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ShipmentCollection::class;
    }

    public function getEntityClass(): string
    {
        return ShipmentEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class))->addFlags(new Required()),
            // (new ManyToOneAssociationField('shippingOption', 'shipping_option_id', ShippingOptionDefinition::class))->addFlags(new Required()),
        ]);
    }
}