<?php

namespace MyPa\Shopware\Service\Shipment;

use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class InsuranceService
{
    /** @var SystemConfigService */
    private $systemConfigService;

    /** @var EntityRepository */
    private $countryRepository;

    public function __construct(SystemConfigService $systemConfigService, EntityRepository $countryRepository)
    {
        $this->systemConfigService = $systemConfigService;
        $this->countryRepository = $countryRepository;
    }

    public function getInsuranceAmount($cartTotal, CountryEntity $country, $carrierId)
    {
        if(!$this->systemConfigService->get('MyPaShopware.config.myParcelShipInsured')){
            return 0;
        }

        if($cartTotal < $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredFromAmount')){
            return 0;
        }

        if($carrierId == PostNLConsignment::CARRIER_ID){
            $countryId = $this->systemConfigService->get('MyPaShopware.config.defaultShipFromCountry');

            $fromCountry = $this->countryRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('id', $countryId))
            )->first();

            if($fromCountry->getIso() == 'NL' && $country->getIso() == 'NL'){
                return $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredMaxAmount');
            } else if ($fromCountry->getIso() == 'NL' && $country->getIso() == 'BE'){
                return 500;
            }
            if($country->getIso() == 'BE'){
                return 500;
            }
        }

        if($carrierId == BpostConsignment::CARRIER_ID){
            return 500;
        }

        return 0;
    }

}
