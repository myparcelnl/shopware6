<?php

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingOption\ShippingOptionEntity;
use MyPa\Shopware\Service\Config\ConfigReader;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartConversionSubscriber implements EventSubscriberInterface
{
    /**
     * ShippingOptionEntity::FIELD_DELIVERY_TYPE is not needed because it will always be read from the checkout settings
     * ShippingOptionEntity::FIELD_PACKAGE_TYPE is not needed beacuse it will always be read from the checkout settings
     * ShippingOptionEntity::FIELD_ONLY_RECIPIENT will only be read in defaults, not from checkout
     */
    const SHIPPING_OPTIONS_WITH_DEFAULT = [ShippingOptionEntity::FIELD_DELIVERY_DATE => "",
        ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK => "myParcelDefaultAgeCheck", ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE => "myParcelDefaultSignature",
        ShippingOptionEntity::FIELD_ONLY_RECIPIENT => "myParcelDefaultOnlyRecipient", ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME => "myParcelDefaultReturnNotHome",
        ShippingOptionEntity::FIELD_LARGE_FORMAT => "myParcelDefaultLargeFormat"];

    const SHIPPING_OPTIONS_FOR_DATA_KEY = [];
    const CARRIER_TO_ID = ['postnl' => 1, 'bpost' => 2, 'cheapcargo' => 3, 'dpd' => 4, 'instabox' => 5, 'dhl' => 6];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigReader
     */
    private $configReader;

    public function __construct(LoggerInterface $logger, ConfigReader $configReader)
    {
        $this->logger = $logger;
        $this->configReader = $configReader;
    }

    public static function getSubscribedEvents()
    {
        return [CartConvertedEvent::class => 'cartConverted'];
    }

    public function cartConverted(CartConvertedEvent $event)
    {
        dump($event);
        //TODO: check if it is a myparcel shipping
        $myParcelData = $event->getCart()->getExtension('myparcel-data');
        $options = $this->setGeneralDefaults($event->getSalesChannelContext()->getSalesChannelId());
        //Cart extension data
        if (!empty($myParcelData) && !empty($myParcelData['myparcel']['deliveryData'])) {
            foreach ($myParcelData['myparcel']['deliveryData'] as $key => $value) {
                switch ($key) {
                    case 'deliveryType':
                        $options[ShippingOptionEntity::FIELD_DELIVERY_TYPE] = $this->deliveryTypeToInt($value);
                        break;

                    case 'date':
                        $strDate = \strtotime($value);
                        if (!$strDate) {
                            $strDate = strtotime("+1 day");
                        }
                        $options[ShippingOptionEntity::FIELD_DELIVERY_DATE] = \date('Y-m-d', $strDate);
                        break;

                    case 'shipmentOptions':
                        //Check if the option was even shown via 'allowSignature' and 'allowOnlyRecipient', otherwise use the given value
                        if ($this->configReader->isSettingEnabled($event->getSalesChannelContext()->getSalesChannelId(), 'allowOnlyRecipient', '')) {
                            $options[ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME] = $value['only_recipient'];
                        }
                        if ($this->configReader->isSettingEnabled($event->getSalesChannelContext()->getSalesChannelId(), 'allowSignature', '')) {
                            $options[ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE] = $value['signature'];
                        }
                        break;

                    case 'carrier':
                        $options[ShippingOptionEntity::FIELD_CARRIER_ID] = self::CARRIER_TO_ID[$value];
                        break;

                    case 'pickupLocation':
                        $options[ShippingOptionEntity::FIELD_PICKUP_LOCATION_ID] = intval($value['location_code']);
                        $options[ShippingOptionEntity::FIELD_PICKUP_NAME] = $value['location_name'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_STREET] = $value['street'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_NUMBER] = $value['number'].$value['number_suffix'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_POSTAL_CODE] = $value['postal_code'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_CITY] = $value['city'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_CC] = $value['cc'];
                        $options[ShippingOptionEntity::FIELD_RETAIL_NETWORK_ID] = $value['retail_network_id'];
                        $options[ShippingOptionEntity::FIELD_RETAIL_NETWORK_ID] = $value['retail_network_id'];
                        break;
                }
            }
        }
        //Rest of the data
        if (!empty($options)) {
            // Add the order to the shipping options
            $options[ShippingOptionEntity::FIELD_ORDER] = [
                'id' => $event->getOrder()->getId(),
                'versionId' => $event->getOrder()->getVersionId(),
            ];
        }
        dump($options);
    }

    /**
     * Sets all the defaults for values that will not be filled but the implementation of createOrUpdateShippingOptions()
     * still needs.
     * @param string $salesChannelId
     * @return array
     */
    private function setGeneralDefaults(string $salesChannelId): array
    {
        $options = [];

        foreach (self::SHIPPING_OPTIONS_WITH_DEFAULT as $key => $value) {
            switch ($key) {
                case ShippingOptionEntity::FIELD_DELIVERY_DATE:
                    $options[ShippingOptionEntity::FIELD_DELIVERY_DATE] = \date('Y-m-d', strtotime("+1 day"));
                    break;
                case ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK:
                    $options[ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK] = $this->configReader->getConfigBool($salesChannelId, $value);
                    break;
                case ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE:
                    $options[ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE] = $this->configReader->getConfigBool($salesChannelId, $value);
                    break;
                case ShippingOptionEntity::FIELD_ONLY_RECIPIENT:
                    $options[ShippingOptionEntity::FIELD_ONLY_RECIPIENT] = $this->configReader->getConfigBool($salesChannelId, $value);
                    break;
                case ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME:
                    $options[ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME] = $this->configReader->getConfigBool($salesChannelId, $value);
                    break;
                case ShippingOptionEntity::FIELD_LARGE_FORMAT:
                    $options[ShippingOptionEntity::FIELD_LARGE_FORMAT] = $this->configReader->getConfigBool($salesChannelId, $value);
                    break;
            }
        }
        return $options;
    }

    private function deliveryTypeToInt(string $type): int
    {
        switch ($type) {
            case 'morning':
                return 1;
            case 'evening':
                return 3;
            default:
                return 2;
        }
    }

    private function carrierToInt()
    {

    }
}
