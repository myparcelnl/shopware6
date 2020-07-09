<?php


namespace Kiener\KienerMyParcel\Service\Settings;

use Kiener\KienerMyParcel\Setting\MyParcelSettingStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SettingsService
{
    public const SYSTEM_CONFIG_DOMAIN = 'KienerMyParcel.config.';

    /**
     * @var SystemConfigService
     */
    protected $systemConfigService;

    /**
     * SettingsService constructor.
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(
        SystemConfigService $systemConfigService
    )
    {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Get MyParcel settings from configuration.
     *
     * @param string|null  $salesChannelId
     *
     * @return MyParcelSettingStruct
     */
    public function getSettings(?string $salesChannelId = null): MyParcelSettingStruct
    {
        $structData = [];
        $systemConfigData = $this->systemConfigService->getDomain(self::SYSTEM_CONFIG_DOMAIN, $salesChannelId, true);

        foreach ($systemConfigData as $key => $value) {
            if (stripos($key, self::SYSTEM_CONFIG_DOMAIN) !== false) {
                $structData[substr($key, strlen(self::SYSTEM_CONFIG_DOMAIN))] = $value;
            } else {
                $structData[$key] = $value;
            }
        }

        return (new MyParcelSettingStruct())->assign($structData);
    }
}