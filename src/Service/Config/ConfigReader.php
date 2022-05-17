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
    public function getConfigForPackage(): array
    {
        $config = [];
        $config = array_merge($config, $this->getGeneralSettings());
        $config['carrierSettings'] = $this->getCarrierSettings();
        return $config;
    }

    private function getGeneralSettings(): array
    {
        $settings = [
            "platform" => $this->systemConfigService->getString('MyPaShopware.config.platform'),
        ];
        return array_merge($settings, $this->generateConfig());
    }

    private function generateConfig(string $carrier = ''): array
    {
        $settingsToRetrieve = ['priceMorningDelivery', 'priceStandardDelivery', 'priceSameDayDelivery',
            'priceEveningDelivery', 'priceSignature', 'priceOnlyRecipient',
            'pricePickup', 'allowSaturdayDelivery', 'allowPickupLocations',
            'allowSignature', 'deliveryDaysWindow', 'dropOffDelay'];

        $settings = [];

        foreach ($settingsToRetrieve as $settingToRetrieve) {
            //Check if the setting is enabled, general settings have no enabled flag
            if ($this->systemConfigService->getBool('MyPaShopware.config.' . $settingToRetrieve . 'Enabled' . $carrier) || $carrier == '') {
                $setting = $this->getConfigValue($settingToRetrieve, $carrier);
                if ($setting !== null) {
                    $settings[$settingToRetrieve] = $setting;
                }
            }
        }

        if (
            $this->getConfigValue('dropOffDays', $carrier) != null &&
            $this->systemConfigService->getBool('MyPaShopware.config.dropOffDaysEnabled' . $carrier)
        ) {
            $settings["dropOffDays"] = implode(";", $this->getConfigValue('dropOffDays', $carrier));
        }

        if (!empty($this->getConfigString('cutoffTime', $carrier)) &&
            $this->systemConfigService->getBool('MyPaShopware.config.cutoffTimeEnabled' . $carrier)
        ) {
            $settings["cutoffTime"] = substr($this->getConfigString('cutoffTime', $carrier), 0, -3);
        }

        return $settings;
    }

    private function getConfigValue(string $field, string $carrier = "")
    {
        return $this->systemConfigService->get('MyPaShopware.config.' . $field . $carrier);
    }

    private function getConfigString(string $field, string $carrier = "")
    {
        return $this->systemConfigService->getString('MyPaShopware.config.' . $field . $carrier);
    }

    private function getCarrierSettings(): array
    {
        $carriers = MyParcelCarriers::ALL_CARRIERS;
        $result = [];
        foreach ($carriers as $carrier) {
            if ($this->getConfigBool('enabled', $carrier)) {
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
                $result[$carrierNPMConfigName] = $this->generateConfig($shopwareConfigCarrierName);
            }
        }
        return $result;
    }

    private function getConfigBool(string $field, string $carrier = "")
    {
        return $this->systemConfigService->getBool('MyPaShopware.config.' . $field . $carrier);
    }

    private function getConfigFloat(string $field, string $carrier = "")
    {
        return $this->systemConfigService->getFloat('MyPaShopware.config.' . $field . $carrier);
    }

    private function getConfigInt(string $field, string $carrier = "")
    {
        return $this->systemConfigService->getInt('MyPaShopware.config.' . $field . $carrier);
    }
}
