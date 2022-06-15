<?php

namespace MyPa\Shopware\Service\Config;

abstract class MyParcelCarriers
{
    const POSTNL = 'PostNL';
    const DHL = 'DHL';
    const INSTABOX = 'Instabox';
    const BPOST = 'Bpost';
    const DPD = 'Dpd';
    const ALL_CARRIERS = [self::POSTNL,self::DHL,self::INSTABOX,self::BPOST,self::DPD];
    const NPM_CARRIER_TO_CONFIG_CARRIER = [
        'postnl' =>self::POSTNL,
        'dhl' =>self::DHL,
        'instabox' =>self::INSTABOX,
        'bpost' =>self::BPOST,
        'dpd' =>self::DPD,
    ];
    const CONFIG_CARRIER_TO_NPM_CARRIER = [
        self::POSTNL=>'postnl',
        self::DHL=>'dhl',
        self::INSTABOX=>'instabox',
        self::BPOST=>'bopost',
        self::DPD=>'dpd',
    ];
}
