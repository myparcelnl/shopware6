<?php

namespace MyPa\Shopware\Service\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigReader
{
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
        $settings = [
            "platform" => $this->systemConfigService->getString('MyPaShopware.config.platform', $salesChannelId),
        ];
        return array_merge($settings, $this->generateConfig($salesChannelId));
    }

    private function generateConfig(string $salesChannelId, string $carrier = ''): array
    {
        $settingsToRetrieve = ['priceMorningDelivery', 'priceStandardDelivery', 'priceSameDayDelivery',
            'priceEveningDelivery', 'priceSignature', 'priceOnlyRecipient',
            'pricePickup', 'allowSaturdayDelivery', 'allowPickupLocations',
            'allowSignature', 'deliveryDaysWindow', 'dropOffDelay'];

        $settings = [];

        foreach ($settingsToRetrieve as $settingToRetrieve) {
            //Check if the setting is enabled, general settings have no enabled flag
            if ($this->systemConfigService->getBool('MyPaShopware.config.' . $settingToRetrieve . 'Enabled' . $carrier, $salesChannelId) || $carrier == '') {
                $setting = $this->getConfigValue($salesChannelId, $settingToRetrieve, $carrier);
                if ($setting !== null) {
                    $settings[$settingToRetrieve] = $setting;
                }
            }
        }

        if (
            $this->getConfigValue($salesChannelId, 'dropOffDays', $carrier) != null &&
            $this->systemConfigService->getBool('MyPaShopware.config.dropOffDaysEnabled' . $carrier, $salesChannelId)
        ) {
            $settings["dropOffDays"] = implode(";", $this->getConfigValue($salesChannelId, 'dropOffDays', $carrier));
        }

        if (!empty($this->getConfigString($salesChannelId, 'cutoffTime', $carrier)) &&
            $this->systemConfigService->getBool('MyPaShopware.config.cutoffTimeEnabled' . $carrier, $salesChannelId)
        ) {
            $settings["cutoffTime"] = substr($this->getConfigString($salesChannelId, 'cutoffTime', $carrier), 0, -3);
        }
        $settings['allowShowDeliveryDate'] = true;//TODO: Bugfix delete when fixed on NPM side or change to force true
        return $settings;
    }

    private function getConfigValue(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->get('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    private function getConfigString(string $salesChannelId, string $field, string $carrier = "")
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

    private function getConfigBool(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getBool('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    private function getConfigFloat(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getFloat('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }

    private function getConfigInt(string $salesChannelId, string $field, string $carrier = "")
    {
        return $this->systemConfigService->getInt('MyPaShopware.config.' . $field . $carrier, $salesChannelId);
    }
}
