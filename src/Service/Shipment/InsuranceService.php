<?php

namespace MyPa\Shopware\Service\Shipment;

use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class InsuranceService
{
    /** @var SystemConfigService */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getInsuranceAmount($cartTotal, $countryCode, $carrierId)
    {
        if(!$this->systemConfigService->get('MyPaShopware.config.myParcelShipInsured')){
            return 0;
        }

        if($cartTotal < $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredFromAmount')){
            return 0;
        }

        if($carrierId == PostNLConsignment::CARRIER_ID){
            if($countryCode == 'NL'){
                return $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredMaxAmount');
            }
            if($countryCode == 'BE'){
                return 500;
            }
        }

        if($carrierId == BpostConsignment::CARRIER_ID){
            return 500;
        }

        return 0;

    }

}
