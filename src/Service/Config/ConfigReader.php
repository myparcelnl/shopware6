<?php

namespace MyPa\Shopware\Service\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigReader
{
    const ALWAYS_ENABLED_SETTINGS = ['allowShowDeliveryDate', 'allowMorningDelivery', 'allowSaturdayDelivery', 'allowPickupLocations',
        'allowSignature', 'allowEveningDelivery', 'allowOnlyRecipient','allowOnlyRecipient'];

    private SystemConfigService $systemConfigService;

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @return array An array with the settings for the NPM package
     */
    public function getConfigForPackage(string $salesChannelId): array
    {
        $config = [];
        $config = array_merge($config, $this->getGeneralSettings($salesChannelId));
        $config['carrierSettings'] = $this->getCarrierSettings($salesChannelId);
        return $config;
    }

    private function getGeneralSettings(string $salesChannelId): array
    {
        //These are the settings that are only valid for the main config
        $settings = [
            "platform" => $this->systemConfigService->getString('MyPaShopware.config.platform', $salesChannelId),
            "packageType" => $this->systemConfigService->getString('MyPaShopware.config.packageType', $salesChannelId),
        ];
        return array_merge($settings, $this->generateConfig($salesChannelId));
    }

    private function generateConfig(string $salesChannelId, string $carrier = ''): array
    {
        $settingsToRetrieve = ['allowShowDeliveryDate', 'allowMorningDelivery',
            'priceMorningDelivery', 'priceStandardDelivery', 'priceSameDayDelivery',
            'allowEveningDelivery', 'priceEveningDelivery', 'priceSignature',
            'allowOnlyRecipient', 'priceOnlyRecipient',
            'pricePickup', 'allowSaturdayDelivery', 'allowPickupLocations',
            'allowSignature','allowOnlyRecipient', 'deliveryDaysWindow', 'dropOffDelay'];

        $settings = [];

        foreach ($settingsToRetrieve as $settingToRetrieve) {
            //Check if the setting is enabled, general settings have no enabled flag
            if ($this->isSettingEnabled($salesChannelId, $settingToRetrieve, $carrier)) {
                $setting = $this->getConfigValue($salesChannelId, $settingToRetrieve, $carrier);
                if ($setting !== null) {
                    $settings[$settingToRetrieve] = $setting;
                }
            }
        }

        if (
            $this->getConfigValue($salesChannelId, 'dropOffDays', $carrier) !== null && $this->isSettingEnabled($salesChannelId,'dropOffDays',$carrier)
        ) {
            $settings["dropOffDays"] = implode(";", $this->getConfigValue($salesChannelId, 'dropOffDays', $carrier));
        }

        if (!empty($this->getConfigString($salesChannelId, 'cutoffTime', $carrier)) && $this->isSettingEnabled($salesChannelId, 'cutoffTime', $carrier)
        ) {
            $settings["cutoffTime"] = substr($this->getConfigString($salesChannelId, 'cutoffTime', $carrier), 0, -3);
        }
        return $settings;
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

    private function getCarrierSettings(string $salesChannelId): array
    {
        $carriers = MyParcelCarriers::ALL_CARRIERS;
        $result = [];
        foreach ($carriers as $carrier) {
            if ($this->getConfigBool($salesChannelId, 'enabled', $carrier)) {
                $carrierNPMConfigName = "";
                $shopwareConfigCarrierName = "";
                switch ($carrier) {
                    case MyParcelCarriers::POSTNL:
                        $carrierNPMConfigName = 'postnl';
                        $shopwareConfigCarrierName = MyParcelCarriers::POSTNL;
                        break;
                    case MyParcelCarriers::DPD:
                        $carrierNPMConfigName = 'dpd';
                        $shopwareConfigCarrierName = MyParcelCarriers::DPD;
                        break;
                    case MyParcelCarriers::BPOST:
                        $carrierNPMConfigName = 'bpost';
                        $shopwareConfigCarrierName = MyParcelCarriers::BPOST;
                        break;
                    case MyParcelCarriers::INSTABOX:
                        $carrierNPMConfigName = 'instabox';
                        $shopwareConfigCarrierName = MyParcelCarriers::INSTABOX;
                        break;
                    case MyParcelCarriers::DHL:
                        $carrierNPMConfigName = 'dhl';
                        $shopwareConfigCarrierName = MyParcelCarriers::DHL;
                        break;
                }
                $result[$carrierNPMConfigName] = $this->generateConfig($salesChannelId, $shopwareConfigCarrierName);
            }
        }
        return $result;
    }

    public function getConfigBool(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getBool('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    public function getConfigFloat(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getFloat('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    public function getConfigInt(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getInt('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }
}
