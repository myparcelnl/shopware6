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
}
