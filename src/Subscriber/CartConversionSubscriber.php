<?php

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingOption\ShippingOptionEntity;
use MyPa\Shopware\Defaults;
use MyPa\Shopware\Service\Config\ConfigGenerator;
use MyPa\Shopware\Service\Config\MyParcelCarriers;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartConversionSubscriber implements EventSubscriberInterface
{
    /**
     * ShippingOptionEntity::FIELD_DELIVERY_TYPE is not needed because it will always be read from the checkout settings
     * ShippingOptionEntity::FIELD_ONLY_RECIPIENT will only be read in defaults, not from checkout
     */
    private const SHIPPING_OPTIONS_WITH_DEFAULT = [
        ShippingOptionEntity::FIELD_DELIVERY_DATE      => "",
        ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK => "myParcelDefaultAgeCheck",
        ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE => "myParcelDefaultSignature",
        ShippingOptionEntity::FIELD_ONLY_RECIPIENT     => "myParcelDefaultOnlyRecipient",
        ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME => "myParcelDefaultReturnNotHome",
        ShippingOptionEntity::FIELD_LARGE_FORMAT       => "myParcelDefaultLargeFormat",
        ShippingOptionEntity::FIELD_PACKAGE_TYPE       => "myParcelDefaultPackageType",
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigGenerator
     */
    private $configReader;

    /**
     * @param LoggerInterface $logger
     * @param ConfigGenerator $configReader
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigGenerator $configReader
    )
    {
        $this->logger = $logger;
        $this->configReader = $configReader;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            CartConvertedEvent::class => 'cartConverted',
        ];
    }

    /**
     * @param CartConvertedEvent $event
     * @return void
     */
    public function cartConverted(CartConvertedEvent $event)
    {
        if (empty($event->getCart()->getExtension(Defaults::CART_EXTENSION_KEY))) {
            return;
        }
        $myParcelData = $event->getCart()->getExtension(Defaults::CART_EXTENSION_KEY)->getVars();
        $options = $this->setGeneralDefaults($event->getSalesChannelContext()->getSalesChannelId());

        //Cart extension data check if it is a myparcel shipping
        if (!empty($myParcelData) && !empty($myParcelData['myparcel']['deliveryData'])) {

            /** @var \stdClass $deliveryData */
            $deliveryData = $myParcelData['myparcel']['deliveryData'];
            $deliveryData = json_decode(json_encode($deliveryData), true);

            foreach ($deliveryData as $key => $value) {
                switch ($key) {
                    case 'deliveryType':
                        $options[ShippingOptionEntity::FIELD_DELIVERY_TYPE] = AbstractConsignment::DELIVERY_TYPES_NAMES_IDS_MAP[$value ?? 'standard'];
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
                        $carrierId = $deliveryData['carrier'];

                        if ($this->configReader->isSettingEnabled(
                                $event->getSalesChannelContext()->getSalesChannelId(),
                                'allowOnlyRecipient',
                                MyParcelCarriers::NPM_CARRIER_TO_CONFIG_CARRIER[$carrierId]
                            ) &&
                            isset($value['only_recipient'])) {
                            $options[ShippingOptionEntity::FIELD_ONLY_RECIPIENT] = $value['only_recipient'];
                        }

                        if ($this->configReader->isSettingEnabled(
                                $event->getSalesChannelContext()->getSalesChannelId(),
                                'allowSignature',
                                MyParcelCarriers::NPM_CARRIER_TO_CONFIG_CARRIER[$carrierId]
                            ) &&
                            isset($value['signature'])) {
                            $options[ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE] = $value['signature'];
                        }
                        break;

                    case 'carrier':
                        $options[ShippingOptionEntity::FIELD_CARRIER_ID] = Defaults::CARRIER_TO_ID[$value];
                        break;

                    case 'pickupLocation':
                        $options[ShippingOptionEntity::FIELD_PICKUP_LOCATION_ID] = intval($value['location_code']);
                        $options[ShippingOptionEntity::FIELD_PICKUP_NAME] = $value['location_name'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_STREET] = $value['street'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_NUMBER] = $value['number'] . $value['number_suffix'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_POSTAL_CODE] = $value['postal_code'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_CITY] = $value['city'];
                        $options[ShippingOptionEntity::FIELD_PICKUP_CC] = $value['cc'];
                        $options[ShippingOptionEntity::FIELD_RETAIL_NETWORK_ID] = $value['retail_network_id'];
                        break;
                    case 'packageType':
                        $options[ShippingOptionEntity::FIELD_PACKAGE_TYPE] = AbstractConsignment::PACKAGE_TYPES_NAMES_IDS_MAP[$value ?? 'package'];
                }
            }
        }

        //Rest of the data will be done in OrderPlacedSubscriber, this ferries the data there
        $event->setConvertedCart(array_merge($event->getConvertedCart(), ['customFields' => [Defaults::MYPARCEL_DELIVERY_OPTIONS_KEY => $options]]));
    }

    /**
     * Sets all the defaults for values that will not be filled but the implementation of createOrUpdateShippingOptions()
     * still needs.
     *
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
                case ShippingOptionEntity::FIELD_PACKAGE_TYPE:
                    $options[ShippingOptionEntity::FIELD_PACKAGE_TYPE] = $this->configReader->getConfigInt($salesChannelId, $value);
                    break;
            }
        }
        return $options;
    }
}
