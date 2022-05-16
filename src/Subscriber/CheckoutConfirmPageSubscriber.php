<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Service\Config\ConfigReader;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmPageSubscriber implements EventSubscriberInterface
{

    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @param SystemConfigService $configService
     * @param ConfigReader $configReader
     */
    public function __construct(SystemConfigService $configService, ConfigReader $configReader)
    {
        $this->configService = $configService;
        $this->configReader = $configReader;
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
        //Add config data
//        $event->getPage()->assign($data);
        $event->getPage()->addArrayExtension('myparcel', ['config'=>$this->configReader->getConfigForPackage()]);
    }
}
