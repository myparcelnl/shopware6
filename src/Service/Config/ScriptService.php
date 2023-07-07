<?php

namespace MyPa\Shopware\Service\Config;

class ScriptService
{
    /**
     * @return string
     */
    private function getDeliveryOptionsVersion(): string
    {
        return '^5';
    }

    /**
     * @return string
     */
    public function getDeliveryOptionsCdnUrl(): string
    {
        return sprintf(
            'https://unpkg.com/@myparcel/delivery-options@%s/dist/myparcel.js',
            urlencode($this->getDeliveryOptionsVersion())
        );
    }
}
