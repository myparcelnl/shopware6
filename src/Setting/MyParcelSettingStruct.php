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


}