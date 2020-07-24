<?php declare(strict_types = 1);

namespace Kiener\KienerMyParcel\Subscriber;

use Kiener\KienerMyParcel\Service\Settings\SettingsService;
use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Kiener\KienerMyParcel\Setting\MyParcelSettingStruct;
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
     * @var SettingsService
     */
    private $settingsService;

    /**
     * Creates a new instance of the checkout confirm page subscriber.
     *
     * @param ShippingMethodService $shippingMethodService
     * @param SettingsService       $settingsService
     */
    public function __construct(
        ShippingMethodService $shippingMethodService,
        SettingsService $settingsService
    )
    {
        $this->shippingMethodService = $shippingMethodService;
        $this->settingsService = $settingsService;
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
        /** @var MyParcelSettingStruct $settings */
        $settings = $this->settingsService->getSettings(
            $args->getSalesChannelContext()->getSalesChannel()->getId()
        );

        $options = [
            'myparcel_shipping_method_ids' => $this->shippingMethodService->getMyParcelShippingMethodIds(
                $args->getContext()
            ),
        ];

        $shippingCostsPrice = $args->getPage()->getCart()->getShippingCosts()->getTotalPrice();

        if ($settings !== null) {
            $options['my_parcel_morning_delivery_cost'] = $settings->getCostsDeliveryMorning() - $shippingCostsPrice;
            $options['my_parcel_standard_delivery_cost'] = 0;
            $options['my_parcel_evening_delivery_cost'] = $settings->getCostsDeliveryEvening() - $shippingCostsPrice;
            $options['my_parcel_pickup_delivery_cost'] = 0;
        }

        $args->getPage()->assign($options);
    }
}