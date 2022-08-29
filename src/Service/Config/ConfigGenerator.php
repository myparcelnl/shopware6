<?php

namespace MyPa\Shopware\Service\Config;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigGenerator
{
    const ALWAYS_ENABLED_SETTINGS = ['allowShowDeliveryDate', 'allowMorningDelivery', 'allowSaturdayDelivery', 'allowPickupLocations',
        'allowSignature', 'allowEveningDelivery', 'allowOnlyRecipient', 'allowOnlyRecipient'];

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
     * @param array $options
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
        } else {
            $totalPrice = $this->addPriceForSetting(
                $salesChannelId,
                sprintf('price%sDelivery', ucfirst($options['deliveryType'])),
                $carrier,
                $totalPrice
            );

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

        }
        return $totalPrice;
    }

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
    public function generateConfigForPackage(SalesChannelContext $salesChannelContext,string $locale): array
    {
        $config = [];
        $config = array_merge($config, $this->getGeneralSettings($salesChannelContext,$locale));
        $config['carrierSettings'] = $this->getCarrierSettings($salesChannelContext->getSalesChannelId());
        return $config;
    }

    private function getGeneralSettings(SalesChannelContext $salesChannelContext,string $locale): array
    {
        //These are the settings that are only valid for the main config
        $settings = [
            "platform" => $this->systemConfigService->getString('MyPaShopware.config.platform', $salesChannelContext->getSalesChannelId()),
            "packageType" => $this->systemConfigService->getString('MyPaShopware.config.packageType', $salesChannelContext->getSalesChannelId()),
            "currency"=>$salesChannelContext->getCurrency()->getIsoCode(),
            "locale"=>$locale
        ];
        return array_merge($settings, $this->generateConfig($salesChannelContext->getSalesChannelId()));
    }

    private function generateConfig(string $salesChannelId, string $carrier = ''): array
    {
        $settingsToRetrieve = ['allowShowDeliveryDate', 'allowMorningDelivery',
            'priceMorningDelivery', 'priceStandardDelivery', 'priceSameDayDelivery',
            'allowEveningDelivery', 'priceEveningDelivery', 'priceSignature',
            'allowOnlyRecipient', 'priceOnlyRecipient',
            'pricePickup', 'allowSaturdayDelivery', 'allowPickupLocations',
            'allowSignature', 'allowOnlyRecipient', 'deliveryDaysWindow', 'dropOffDelay'];

        $settings = [];

        foreach ($settingsToRetrieve as $settingToRetrieve) {
            //Check if the setting is enabled
            if ($this->isSettingEnabled($salesChannelId, $settingToRetrieve, $carrier)) {
                $setting = $this->getConfigValue($salesChannelId, $settingToRetrieve, $carrier);
                if ($setting !== null) {
                    $settings[$settingToRetrieve] = $setting;
                }
            }
        }

        if (
            $this->getConfigValue($salesChannelId, 'dropOffDays', $carrier) !== null && $this->isSettingEnabled($salesChannelId, 'dropOffDays', $carrier)
        ) {
            $settings["dropOffDays"] = implode(";", $this->getConfigValue($salesChannelId, 'dropOffDays', $carrier));
        }

        if (!empty($this->getConfigString($salesChannelId, 'cutoffTime', $carrier)) && $this->isSettingEnabled($salesChannelId, 'cutoffTime', $carrier)
        ) {
            $settings["cutoffTime"] = substr($this->getConfigString($salesChannelId, 'cutoffTime', $carrier), 0, -3);
        }
        return $settings;
    }

    private function getCarrierSettings(string $salesChannelId): array
    {
        $carriers = MyParcelCarriers::ALL_CARRIERS;
        $result = [];

        foreach ($carriers as $carrier) {
            if ($this->getConfigBool($salesChannelId, 'enabled', $carrier)) {

                $carrierNPMConfigName = MyParcelCarriers::CONFIG_CARRIER_TO_NPM_CARRIER[$carrier];
                $shopwareConfigCarrierName = $carrier;

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
        return $this->systemConfigService->getBool('MyPaShopware.config.' . $field . 'Enabled' . $carrier, $salesChannelId);
    }

    public function getConfigValue(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->get('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    public function getConfigString(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getString('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    public function getConfigBool(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getBool('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    public function getConfigInt(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getInt('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    public function getConfigFloat(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getFloat('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }


}
