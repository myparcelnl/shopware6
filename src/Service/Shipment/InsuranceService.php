<?php

namespace MyPa\Shopware\Service\Shipment;

use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use Shopware\Core\Framework\Context;
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
        SystemConfigService       $systemConfigService,
        EntityRepositoryInterface $countryRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @param               $cartTotal
     * @param CountryEntity $country
     * @param               $carrierId
     * @param Context       $context
     * @return array|bool|float|int|string|null
     */
    public function getInsuranceAmount($cartTotal, CountryEntity $country, $carrierId, Context $context)
    {
        if (!$this->systemConfigService->get('MyPaShopware.config.myParcelShipInsured')) {
            return 0;
        }

        if ($cartTotal < $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredFromAmount')) {
            return 0;
        }

        if ($carrierId == PostNLConsignment::CARRIER_ID) {
            $countryId = $this->systemConfigService->get('MyPaShopware.config.defaultShipFromCountry');

            $fromCountry = $this->countryRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('id', $countryId))
                , $context)->first();

            if ($fromCountry->getIso() == 'NL' && $country->getIso() == 'NL') {
                return $this->systemConfigService->get('MyPaShopware.config.myParcelShipInsuredMaxAmount');
            } else if ($fromCountry->getIso() == 'NL' && $country->getIso() == 'BE') {
                return 500;
            }
            if ($country->getIso() == 'BE') {
                return 500;
            }
        }

        if ($carrierId == BpostConsignment::CARRIER_ID) {
            return 500;
        }

        return 0;
    }

}
