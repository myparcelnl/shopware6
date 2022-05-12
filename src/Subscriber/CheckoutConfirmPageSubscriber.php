<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingMethod\ShippingMethodEntity;
use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * Creates a new instance of the checkout confirm page subscriber.
     *
     * @param ShippingMethodService $shippingMethodService
     * @param SystemConfigService $configService
     */
    public function __construct(
        ShippingMethodService $shippingMethodService,
        SystemConfigService   $configService
    )
    {
        $this->shippingMethodService = $shippingMethodService;
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
     * @param PageLoadedEvent|CheckoutConfirmPageLoadedEvent $args
     */
    public function addMyParcelDataToPage($args): void
    {
        $data = [
            'myparcel_shipping_method_ids' => $this->shippingMethodService->getMyParcelShippingMethodIds(
                $args->getContext()
            ),
        ];

        $shippingMethod = $this->shippingMethodService->getShippingMethodByShopwareShippingMethodId(
            $args->getSalesChannelContext()->getShippingMethod()->getId(),
            new Context(new SystemSource())
        );

        if(!($shippingMethod instanceof ShippingMethodEntity)) {
            $data['myparcel_values'] = [
                'deliveryLocation' => 'address',
                'shippingMethodId' => $this->configService->get('MyPaShopware.config.myParcelDefaultMethod'),
                'deliveryDate' => \date('Y-m-d', strtotime("+1 day")),
                'deliveryType' => $this->configService->get('MyPaShopware.config.myParcelDefaultDeliveryWindow'),
                'requiresSignature' => $this->configService->get('MyPaShopware.config.myParcelDefaultSignature'),
                'onlyRecipient' => $this->configService->get('MyPaShopware.config.myParcelDefaultOnlyRecipient')
            ];

            $args->getPage()->assign($data);
            return;
        }

        if($args->getRequest()->cookies->has('myparcel-cookie-key')) {
            $cookie = $args->getRequest()->cookies->get('myparcel-cookie-key');
        }

        if(!isset($cookie) || $cookie == 'empty') {
            $data['myparcel_values'] = [
                'deliveryLocation' => 'address',
                'shippingMethodId' => $args->getSalesChannelContext()->getShippingMethod()->getId(),
                'deliveryDate' => \date('Y-m-d', strtotime("+1 day")),
                'deliveryType' => $this->configService->get('MyPaShopware.config.myParcelDefaultDeliveryWindow'),
                'requiresSignature' => $this->configService->get('MyPaShopware.config.myParcelDefaultSignature'),
                'onlyRecipient' => $this->configService->get('MyPaShopware.config.myParcelDefaultOnlyRecipient')
            ];

            $args->getPage()->assign($data);
            return;
        }

        $cookieData = explode('_', $cookie);

        $data['myparcel_values'] = [
            'deliveryLocation' => $cookieData[0],
            'shippingMethodId' => $cookieData[1],
            'deliveryDate' => $cookieData[2],
            'deliveryType' => $cookieData[3],
            'requiresSignature' => ($cookieData[4] ?? null),
            'onlyRecipient' => ($cookieData[5] ?? null),
            'pickupPointData' => ($cookieData[7] ?? null)
        ];

        $args->getPage()->assign($data);
    }
}
