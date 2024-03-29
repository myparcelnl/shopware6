<?php declare(strict_types=1);

namespace MyPa\Shopware\Core\Content\Shipment;

use MyPa\Shopware\Core\Content\ShippingOption\ShippingOptionDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ShipmentDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'myparcel_shipment';

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
            (new StringField('consignment_reference', 'consignmentReference')),
            (new FkField('shipping_option_id', 'shippingOptionId', ShippingOptionDefinition::class))->addFlags(new CascadeDelete()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new Required(), new CascadeDelete()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required()),
            (new StringField('label_url', 'labelUrl')),
            (new FloatField('insured_amount', 'insuredAmount')),
            (new StringField('bar_code', 'barCode')),
            (new StringField('track_and_trace_url', 'trackAndTraceUrl')),
            (new IntField('shipment_status', 'shipmentStatus')),

            (new ManyToOneAssociationField('shippingOption', 'shipping_option_id', ShippingOptionDefinition::class, 'id', true))->addFlags(new Required()),
            (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', true))->addFlags(new Required(), new CascadeDelete()),
        ]);
    }
}
