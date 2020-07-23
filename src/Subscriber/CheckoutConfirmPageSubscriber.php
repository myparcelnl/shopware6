<?php declare(strict_types = 1);

namespace Kiener\KienerMyParcel\Subscriber;

use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;

class CheckoutConfirmPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /**
     * Creates a new instance of the checkout confirm page subscriber.
     *
     * @param ShippingMethodService $shippingMethodService
     */
    public function __construct(ShippingMethodService $shippingMethodService)
    {
        $this->shippingMethodService = $shippingMethodService;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'addMyParcelShippingMethodIdsToPage',
        ];
    }

    /**
     * Adds an array of MyParcel shipping method ids to the checkout page.
     *
     * @param PageLoadedEvent|CheckoutConfirmPageLoadedEvent $args
     */
    public function addMyParcelShippingMethodIdsToPage($args): void
    {
        $args->getPage()->assign([
            'myparcel_shipping_method_ids' => $this->shippingMethodService->getMyParcelShippingMethodIds($args->getContext()),
            'my_parcel_morning_delivery_cost' => 5.95,
            'my_parcel_standard_delivery_cost' => 0,
            'my_parcel_evening_delivery_cost' => 3.95,
            'my_parcel_pickup_delivery_cost' => 0,
        ]);

    }
}