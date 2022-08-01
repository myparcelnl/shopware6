<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Service\Config\ConfigGenerator;
use Shopware\Core\Framework\Struct\ArrayStruct;
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
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * @param SystemConfigService $configService
     * @param ConfigGenerator     $configGenerator
     */
    public function __construct(SystemConfigService $configService, ConfigGenerator $configGenerator)
    {
        $this->configService = $configService;
        $this->configGenerator = $configGenerator;
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
                ['addMyParcelDataToPage', 500],
            ],
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
        $event->getPage()->addExtension('myparcel', new ArrayStruct([
                'config' => $this->configGenerator->generateConfigForPackage(
                    $event->getSalesChannelContext(),
                    $event->getRequest()->getLocale()
                ),
            ])
        );
    }
}
