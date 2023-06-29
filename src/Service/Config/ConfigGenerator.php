<?php

namespace MyPa\Shopware\Service\Config;

use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Str;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigGenerator
{
    public const ALWAYS_ENABLED_SETTINGS = [
        'allowEveningDelivery',
        'allowMorningDelivery',
        'allowOnlyRecipient',
        'allowPickupLocations',
        'allowSaturdayDelivery',
        'allowShowDeliveryDate',
        'allowSignature',
    ];

    private SystemConfigService $systemConfigService;

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Calculates the cost based on the selected options
     * @param array  $options
     * @param string $salesChannelId
     * @return float
     */
    public function getCostForCarrierWithOptions(array $options, string $salesChannelId): float
    {
        /**
         * Settings with a cost:
         * 'priceMorningDelivery', 'priceStandardDelivery', 'priceEveningDelivery',
         * 'priceSameDayDelivery', 'priceSignature', 'priceOnlyRecipient', 'pricePickup';
         */

        $totalPrice = 0.0;
        //convert npm carrier to config carrier
        $carrier = MyParcelCarriers::NPM_CARRIER_TO_CONFIG_CARRIER[$options['carrier']];

        //Is it pickup?
        if ($options['isPickup']) {
            return $this->addPriceForSetting($salesChannelId, 'pricePickup', $carrier, $totalPrice);
        }

        //Is delivery type morning, standard or evening?
        switch ($options['deliveryType']) {
            case 'morning':
                $totalPrice = $this->addPriceForSetting(
                    $salesChannelId,
                    'priceMorningDelivery',
                    $carrier,
                    $totalPrice);
                break;
            case 'standard':
                $totalPrice = $this->addPriceForSetting(
                    $salesChannelId,
                    'priceStandardDelivery',
                    $carrier,
                    $totalPrice);
                break;
            case 'evening':
                $totalPrice = $this->addPriceForSetting(
                    $salesChannelId,
                    'priceEveningDelivery',
                    $carrier,
                    $totalPrice);
                break;
        }

        if (isset($options['shipmentOptions'])) {
            $shipmentOptions = $options['shipmentOptions'];
            //Does it have Signature
            if (isset($shipmentOptions['signature']) && $shipmentOptions['signature']) {
                $totalPrice = $this->addPriceForSetting(
                    $salesChannelId,
                    'priceSignature',
                    $carrier,
                    $totalPrice);
            }
            //Does it have recipient only?
            if (isset($shipmentOptions['only_recipient']) && $shipmentOptions['only_recipient']) {
                $totalPrice = $this->addPriceForSetting(
                    $salesChannelId,
                    'priceOnlyRecipient',
                    $carrier,
                    $totalPrice);
            }
            //Is it same day?
            if (isset($shipmentOptions['same_day_delivery']) && $shipmentOptions['same_day_delivery']) {
                $totalPrice = $this->addPriceForSetting(
                    $salesChannelId,
                    'priceSameDayDelivery',
                    $carrier,
                    $totalPrice);
            }
        }

        return $totalPrice;
    }

    /**
     * @param  string $salesChannelId
     * @param  string $field
     * @param  string $carrier
     * @param  float  $price
     *
     * @return float
     */
    private function addPriceForSetting(string $salesChannelId, string $field, string $carrier, float $price): float
    {

        if ($this->isSettingEnabled($salesChannelId, $field, '')) {
            $price += $this->getConfigFloat($salesChannelId, $field, '');
        }

        if ($this->isSettingEnabled($salesChannelId, $field, $carrier)) {
            $price += $this->getConfigFloat($salesChannelId, $field, $carrier);
        }

        return $price;
    }

    /**
     * @return array An array with the settings for the NPM package
     */
    public function generateConfigForPackage(SalesChannelContext $salesChannelContext, string $locale): array
    {
        $config                             = [];
        $config                             = array_merge(
            $config,
            $this->getGeneralSettings($salesChannelContext, $locale)
        );
        $config['carrierSettings']          = $this->getCarrierSettings($salesChannelContext->getSalesChannelId());
        $config['translationsFromSettings'] = $this->getDeliveryOptionsStrings(
            $salesChannelContext->getSalesChannelId()
        );

        return $config;
    }

    /**
     * @param  string $salesChannelId
     *
     * @return array
     */
    private function getDeliveryOptionsStrings(string $salesChannelId): array
    {
        $stringsConfig = [];
        $strings = [
            'addressNotFound',
            'city',
            'closed',
            'deliveryEveningTitle',
            'deliveryMorningTitle',
            'deliveryStandardTitle',
            'deliverySameDayTitle',
            'deliveryTitle',
            'from',
            'headerDeliveryOptions',
            'houseNumber',
            'onlyRecipientTitle',
            'openingHours',
            'pickUpFrom',
            'pickupTitle',
            'postcode',
            'retry',
            'signatureTitle',
            'wrongHouseNumberCity',
        ];

        foreach($strings as $string) {
            $stringsConfig[$string] = $this->getConfigString($salesChannelId, $string);
        }

        return $stringsConfig;
    }

    /**
     * @param  \Shopware\Core\System\SalesChannel\SalesChannelContext $salesChannelContext
     * @param  string                                                 $locale
     *
     * @return array
     */
    private function getGeneralSettings(SalesChannelContext $salesChannelContext, string $locale): array
    {
        $cc          = $salesChannelContext->getShippingLocation()->getCountry()->getIso();
        $packageType = (AbstractConsignment::CC_NL === $cc)
            ? $this->systemConfigService->getString('MyPaShopware.config.packageType', $salesChannelContext->getSalesChannelId())
            : AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME;
        $settings = [
            'platform'    => $this->systemConfigService->getString('MyPaShopware.config.platform', $salesChannelContext->getSalesChannelId()),
            'packageType' => $packageType,
            'currency'    => $salesChannelContext->getCurrency()->getIsoCode(),
            'locale'      => $locale,
        ];

        return array_merge($settings, $this->generateConfig($salesChannelContext->getSalesChannelId()));
    }

    /**
     * @param  string $salesChannelId
     * @param  string $carrier
     *
     * @return array
     */
    private function generateConfig(string $salesChannelId, string $carrier = ''): array
    {
        $settingsToRetrieve = [
            'allowShowDeliveryDate',
            'allowMondayDelivery',
            'allowMorningDelivery',
            'priceMorningDelivery',
            'priceStandardDelivery',
            'priceSameDayDelivery',
            'allowEveningDelivery',
            'priceEveningDelivery',
            'priceSignature',
            'allowOnlyRecipient',
            'priceOnlyRecipient',
            'pricePickup',
            'allowSaturdayDelivery',
            'allowPickupLocations',
            'allowSignature',
            'allowOnlyRecipient',
            'deliveryDaysWindow',
            'dropOffDelay',
            'dropOffDays',
        ];

        $settings = ['allowDeliveryOptions' => true];

        foreach ($settingsToRetrieve as $settingToRetrieve) {

            if ($this->isSettingEnabled($salesChannelId, $settingToRetrieve, $carrier)) {
                $setting = $this->getConfigValue($salesChannelId, $settingToRetrieve, $carrier);

                if ($setting !== null) {
                    $settings[$settingToRetrieve] = $setting;
                }
            }
        }

        if (!empty($this->getConfigString($salesChannelId, 'cutoffTime', $carrier)) && $this->isSettingEnabled($salesChannelId, 'cutoffTime', $carrier)
        ) {
            $settings['cutoffTime'] = substr($this->getConfigString($salesChannelId, 'cutoffTime', $carrier), 0, -3);
        }

        return $settings;
    }

    /**
     * @param  string $salesChannelId
     *
     * @return array
     */
    private function getCarrierSettings(string $salesChannelId): array
    {
        $carriers = MyParcelCarriers::ALL_CARRIERS;
        $result   = [];

        foreach ($carriers as $carrier) {
            if ($this->getConfigBool($salesChannelId, 'enabled', $carrier)) {
                $carrierNPMConfigName          = MyParcelCarriers::CONFIG_CARRIER_TO_NPM_CARRIER[$carrier];
                $shopwareConfigCarrierName     = $carrier;
                $result[$carrierNPMConfigName] = $this->generateConfig($salesChannelId, $shopwareConfigCarrierName);
            }
        }

        return $result;
    }

    /**
     * Checks if the setting has been enabled, bool settings will be returned as always enabled
     * @param string $salesChannelId
     * @param string $field
     * @param string $carrier
     * @return bool
     */
    public function isSettingEnabled(string $salesChannelId, string $field, string $carrier = ""): bool
    {
        if (in_array($field, self::ALWAYS_ENABLED_SETTINGS)) {
            return true;
        }

        if (Str::startsWith($field, 'allow')) {
            $carrier = "Enabled$carrier";
        }

        return $this->systemConfigService->getBool("MyPaShopware.config.$field$carrier", $salesChannelId);
    }

    /**
     * @param  string $salesChannelId
     * @param  string $field
     * @param  string $carrier
     *
     * @return mixed
     */
    public function getConfigValue(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->get("MyPaShopware.config.$field$carrier", $salesChannelId);
    }

    /**
     * @param  string $salesChannelId
     * @param  string $field
     * @param  string $carrier
     *
     * @return string
     */
    public function getConfigString(string $salesChannelId, string $field, string $carrier = ''): string
    {
        return $this->systemConfigService->getString("MyPaShopware.config.$field$carrier", $salesChannelId);
    }

    /**
     * @param  string $salesChannelId
     * @param  string $field
     * @param  string $carrier
     *
     * @return bool
     */
    public function getConfigBool(string $salesChannelId, string $field, string $carrier = ''): bool
    {
        return $this->systemConfigService->getBool("MyPaShopware.config.$field$carrier", $salesChannelId);
    }

    /**
     * @param  string $salesChannelId
     * @param  string $field
     * @param  string $carrier
     *
     * @return int
     */
    public function getConfigInt(string $salesChannelId, string $field, string $carrier = ''): int
    {
        return $this->systemConfigService->getInt("MyPaShopware.config.$field$carrier", $salesChannelId);
    }

    /**
     * @param  string $salesChannelId
     * @param  string $field
     * @param  string $carrier
     *
     * @return float
     */
    public function getConfigFloat(string $salesChannelId, string $field, string $carrier = ''): float
    {
        return $this->systemConfigService->getFloat("MyPaShopware.config.$field$carrier", $salesChannelId);
    }
}
