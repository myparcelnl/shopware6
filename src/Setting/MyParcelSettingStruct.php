<?php


namespace Kiener\KienerMyParcel\Setting;

use Shopware\Core\Framework\Struct\Struct;

class MyParcelSettingStruct extends Struct
{
    /**
     * @var string
     */
    protected $myParcelApiKey;

    /**
     * @var float
     */
    protected $costsDeliveryMorning;

    /**
     * @var float
     */
    protected $costsDeliveryEvening;

    /**
     * @return string
     */
    public function getMyParcelApiKey(): string
    {
        return $this->myParcelApiKey;
    }

    /**
     * @param string $myParcelApiKey
     *
     * @return MyParcelSettingStruct
     */
    public function setMyParcelApiKey(string $myParcelApiKey): MyParcelSettingStruct
    {
        $this->myParcelApiKey = $myParcelApiKey;
        return $this;
    }

    /**
     * @return float
     */
    public function getCostsDeliveryMorning(): float
    {
        return $this->costsDeliveryMorning;
    }

    /**
     * @param float $costsDeliveryMorning
     *
     * @return MyParcelSettingStruct
     */
    public function setCostsDeliveryMorning(float $costsDeliveryMorning): MyParcelSettingStruct
    {
        $this->costsDeliveryMorning = $costsDeliveryMorning;
        return $this;
    }

    /**
     * @return float
     */
    public function getCostsDeliveryEvening(): float
    {
        return $this->costsDeliveryEvening;
    }

    /**
     * @param float $costsDeliveryEvening
     *
     * @return MyParcelSettingStruct
     */
    public function setCostsDeliveryEvening(float $costsDeliveryEvening): MyParcelSettingStruct
    {
        $this->costsDeliveryEvening = $costsDeliveryEvening;
        return $this;
    }

}