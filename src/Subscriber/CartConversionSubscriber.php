<?php

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingOption\ShippingOptionEntity;
use MyPa\Shopware\Service\Config\ConfigReader;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartConversionSubscriber implements EventSubscriberInterface
{
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
        $myParcelData = $event->getCart()->getExtension('myparcel-data');
        $options = $this->setDefaults($event->getSalesChannelContext()->getSalesChannelId());
        if (!empty($myParcelData) && !empty($myParcelData['myparcel']['deliveryData'])) {
            foreach ($myParcelData['myparcel']['deliveryData'] as $key => $deliveryDatum) {

            }
        }
    }

    /**
     * Sets all the defaults for values that will not be filled but the implementation of createOrUpdateShippingOptions()
     * still needs.
     * @return void
     */
    private function setDefaults(string $salesChannelId): array
    {
        $options = [];
        //$options[ShippingOptionEntity::FIELD_DELIVERY_TYPE] = $this->configReader->
        //If general version is enabled
        return $options;
    }
}
