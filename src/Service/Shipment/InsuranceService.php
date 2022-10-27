<?php

namespace MyPa\Shopware\Service\Shipment;

use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class InsuranceService
{
    /** @var SystemConfigService */
    private $systemConfigService;

    /** @var EntityRepositoryInterface */
    private $countryRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository    $countryRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->countryRepository   = $countryRepository;
    }

    /**
     * @param                                              $cartTotal
     * @param  \Shopware\Core\System\Country\CountryEntity $country
     * @param                                              $carrierId
     * @param  \Shopware\Core\Framework\Context            $context
     *
     * @return null|array|bool|float|int|string
     */
    public function getInsuranceAmount($cartTotal, CountryEntity $country, $carrierId, Context $context)
    {
        if (! $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsured')
            || $cartTotal < $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredFromAmount')) {
            return 0;
        }

        if ($carrierId === CarrierPostNL::ID) {
            $countryId = $this->systemConfigService->get('MyPaShopware.config.defaultShipFromCountry');

            $fromCountry = $this->countryRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('id', $countryId))
                ,
                $context
            )
                ->first();

            if ($fromCountry->getIso() === 'NL' && $country->getIso() === 'NL') {
                return $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredMaxAmount');
            }

            if ($country->getIso() === 'BE') {
                return 500;
            }
        }

        if ($carrierId === CarrierBpost::ID) {
            return 500;
        }

        return 0;
    }
}
