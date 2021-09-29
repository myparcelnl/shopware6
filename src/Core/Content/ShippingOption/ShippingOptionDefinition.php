<?php

namespace MyPa\Shopware\Core\Content\ShippingOption;

use MyPa\Shopware\Core\Content\Shipment\ShipmentDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ShippingOptionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'kiener_my_parcel_shipping_option';

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return ShippingOptionEntity::class;
    }

    /**
     * @return string
     */
    public function getCollectionClass(): string
    {
        return ShippingOptionCollection::class;
    }

    /**
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new Required(), new CascadeDelete()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required()),
            (new IntField('carrier_id', 'carrierId'))->addFlags(new Required()),
            (new IntField('package_type', 'packageType')),
            (new DateField('delivery_date', 'deliveryDate')),
            (new IntField('delivery_type', 'deliveryType')),
            (new BoolField('requires_age_check', 'requiresAgeCheck')),
            (new BoolField('requires_signature', 'requiresSignature')),
            (new BoolField('only_recipient', 'onlyRecipient')),
            (new BoolField('return_if_not_home', 'returnIfNotHome')),
            (new BoolField('large_format', 'largeFormat')),
            (new IntField('location_id', 'locationId')),
            (new StringField('location_name', 'locationName')),
            (new StringField('location_street', 'locationStreet')),
            (new StringField('location_number', 'locationNumber')),
            (new StringField('location_postalcode', 'locationPostalCode')),
            (new StringField('location_city', 'locationCity')),
            (new StringField('location_cc', 'locationCc')),
            (new StringField('retail_network_id', 'retailNetworkId')),

            (new OneToManyAssociationField('consignments', ShipmentDefinition::class, 'shipping_option_id')),
            (new OneToOneAssociationField('order', 'order_id', 'id', OrderDefinition::class, true))->addFlags(new Required(), new CascadeDelete()),
        ]);
    }
}
