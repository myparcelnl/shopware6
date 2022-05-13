<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingMethod\ShippingMethodEntity;
use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmPageSubscriber implements EventSubscriberInterface
{

    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * @param SystemConfigService $configService
     */
    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => [
                ['addMyParcelDataToPage', 500]
            ]
        ];
    }

    /**
     * Adds an array of MyParcel shipping method ids to the checkout page.
     *
     * @param CheckoutConfirmPageLoadedEvent $event
     */
    public function addMyParcelDataToPage($event): void
    {

//        $event->getPage()->assign($data);
        $event->getPage()->addArrayExtension('test',['test']);
    }
}
