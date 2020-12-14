<?php declare(strict_types = 1);

namespace Kiener\KienerMyParcel\Subscriber;

use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Kiener\KienerMyParcel\Setting\MyParcelSettingStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
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
     * @var SystemConfigService
     */
    private $configService;

    /**
     * Creates a new instance of the checkout confirm page subscriber.
     *
     * @param ShippingMethodService $shippingMethodService
     * @param SystemConfigService       $configService
     */
    public function __construct(
        ShippingMethodService $shippingMethodService,
        SystemConfigService $configService
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
            CheckoutConfirmPageLoadedEvent::class => 'addMyParcelDataToPage',
            CheckoutConfirmPageLoadedEvent::class => 'updateShippingCosts',
        ];
    }

    /**
     * Update the shipping costs displayed based on the MyParcel options selected
     *
     * @param PageLoadedEvent|CheckoutConfirmPageLoadedEvent $args
     */
    public function updateShippingCosts($args): void
    {
        //dd($args->getPage()->getCart());

        //TODO check if the current selected option is a myparcel option

        //TODO if myparcel option then get the cart -> deliveries

        //TODO itterate trough the deliveries

        //TODO get the current delivery price and add the amount that should be applied as raise through the setShippingCosts


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

        if(isset($_COOKIE['myparcel-cookie-key'])){
            $cookie_data = explode('_', $_COOKIE['myparcel-cookie-key']);

            $data['myparcel_values'] = [
                'shippingMethodId' => $cookie_data[0],
                'deliveryDate'=> $cookie_data[1],
                'deliveryType'=> $cookie_data[2],
                'requiresSignature'=> $cookie_data[3],
                'onlyRecipient'=> $cookie_data[4]
            ];
        }else{
            $data['myparcel_values'] = [
                'shippingMethodId' => $this->configService->get('KienerMyParcel.config.myParcelDefaultMethod'),
                'deliveryDate'=> \date('Y-m-d', strtotime("+1 day")),
                'deliveryType'=> $this->configService->get('KienerMyParcel.config.myParcelDefaultDeliveryWindow'),
                'requiresSignature'=> $this->configService->get('KienerMyParcel.config.myParcelDefaultSignature'),
                'onlyRecipient'=> $this->configService->get('KienerMyParcel.config.myParcelDefaultOnlyRecipient')
            ];
        }
        $args->getPage()->assign($data);
    }
}
