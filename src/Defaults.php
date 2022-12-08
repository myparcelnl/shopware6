<?php declare(strict_types=1);

namespace MyPa\Shopware;

class Defaults
{
    const CUSTOM_FIELDS_KEY = 'myparcel';
    const MYPARCEL_DELIVERY_OPTIONS_KEY = 'myparcel-delivery-options';
    const CART_EXTENSION_KEY = 'myparcel-data';

    public const CARRIER_TO_ID = [
        'postnl' => 1,
        'bpost' => 2,
        'cheapcargo' => 3,
        'dpd' => 4,
        'dhl' => 6,
    ];
}
