<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Service\Config\ConfigGenerator;
use MyPa\Shopware\Service\Config\ScriptService;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * @var ScriptService
     */
    private ScriptService $scriptService;

    /**
     * @param  ConfigGenerator $configGenerator
     * @param  ScriptService   $scriptService
     */
    public function __construct(ConfigGenerator $configGenerator, ScriptService $scriptService)
    {
        $this->configGenerator = $configGenerator;
        $this->scriptService   = $scriptService;
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
     * @param  CheckoutConfirmPageLoadedEvent $event
     */
    public function addMyParcelDataToPage(CheckoutConfirmPageLoadedEvent $event): void
    {
        //        Add config data
        $event->getPage()
            ->addExtension(
                'myparcel',
                new ArrayStruct([
                    'config' => $this->configGenerator->generateConfigForPackage(
                        $event->getSalesChannelContext(),
                        $event->getRequest()
                            ->getLocale()
                    ),
                    'deliveryOptionsCdnUrl' => $this->scriptService->getDeliveryOptionsCdnUrl(),
                ])
            );
    }
}
