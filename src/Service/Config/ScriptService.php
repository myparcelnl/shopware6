<?php

namespace MyPa\Shopware\Service\Config;

class ScriptService
{
    /**
     * @return string
     */
    private function getDeliveryOptionsVersion() {
        return '5.8.0';
    }

    /**
     * @return string
     */
    public function getDeliveryOptionsCdnUrl() {
        return sprintf(
            'https://unpkg.com/@myparcel/delivery-options@%s/dist/myparcel.js',
            $this->getDeliveryOptionsVersion()
        );
    }
}
