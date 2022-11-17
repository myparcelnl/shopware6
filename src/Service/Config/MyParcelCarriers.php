<?php

namespace MyPa\Shopware\Service\Config;

abstract class MyParcelCarriers
{
    const POSTNL = 'PostNL';
    const DHL = 'DHL';
    const BPOST = 'BPost';
    const DPD = 'DPD';

    const ALL_CARRIERS = [self::POSTNL,self::DHL,self::BPOST,self::DPD];
    const NPM_CARRIER_TO_CONFIG_CARRIER = [
        'postnl' =>self::POSTNL,
        'dhl' =>self::DHL,
        'bpost' =>self::BPOST,
        'dpd' =>self::DPD,
    ];
    const CONFIG_CARRIER_TO_NPM_CARRIER = [
        self::POSTNL=>'postnl',
        self::DHL=>'dhl',
        self::BPOST=>'bpost',
        self::DPD=>'dpd',
    ];
    const CARRIER_ID_TO_CONFIG_CARRIER = [
        1 =>self::POSTNL,
        2 =>self::DHL,
        4 =>self::BPOST,
        5 =>self::DPD,
    ];
}
