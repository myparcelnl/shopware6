<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingMethod\ShippingMethodEntity;
use MyPa\Shopware\Core\Content\ShippingOption\ShippingOptionEntity;
use MyPa\Shopware\Defaults;
use MyPa\Shopware\Service\Order\OrderService;
use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use MyPa\Shopware\Service\ShippingOptions\ShippingOptionsService;
use MyPa\Shopware\Service\WebhookBuilder\WebhookBuilder;
use MyParcelNL\Sdk\src\Exception\AccountNotActiveException;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Services\Web\Webhook\ShipmentStatusChangeWebhookWebService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderPlacedSubscriber implements EventSubscriberInterface
{
    private const PARAM_MY_PARCEL = 'my_parcel';
    private const PARAM_DELIVERY_DATE = 'delivery_date';
    private const PARAM_DELIVERY_TYPE = 'delivery_type';
    private const PARAM_PACKAGE_TYPE = 'package_type';
    private const PARAM_REQUIRES_AGE_CHECK = 'requires_age_check';
    private const PARAM_REQUIRES_SIGNATURE = 'requires_signature';
    private const PARAM_ONLY_RECIPIENT = 'only_recipient';
    private const PARAM_RETURN_IF_NOT_HOME = 'return_if_not_home';
    private const PARAM_LARGE_FORMAT = 'large_format';
    private const PARAM_SHIPPING_METHOD_ID = 'shipping_method_id';
    private const PARAM_DELIVERY_LOCATION = 'delivery_location';
    private const PARAM_PICKUP_DATA = 'pickup_point_data';
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;
    /**
     * @var ShippingOptionsService
     */
    private $shippingOptionsService;
    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * @var ShipmentStatusChangeWebhookWebService
     */
    private $shipmentStatusChangeWebhookWebService;

    /**
     * @var WebhookBuilder
     */
    private $builder;


    /**
     * Creates a new instance of the order placed subscriber.
     *
     * @param RequestStack $requestStack
     * @param OrderService $orderService
     * @param ShippingMethodService $shippingMethodService
     * @param ShippingOptionsService $shippingOptionService
     * @param SystemConfigService $configService
     * @param LoggerInterface $logger
     * @param ShipmentStatusChangeWebhookWebService $shipmentStatusChangeWebhookWebService
     * @param WebhookBuilder $builder
     */
    public function __construct(
        RequestStack                          $requestStack,
        OrderService                          $orderService,
        ShippingMethodService                 $shippingMethodService,
        ShippingOptionsService                $shippingOptionService,
        SystemConfigService                   $configService,
        LoggerInterface                       $logger,
        ShipmentStatusChangeWebhookWebService $shipmentStatusChangeWebhookWebService,
        WebhookBuilder                        $builder
    )
    {
        $this->requestStack = $requestStack;
        $this->orderService = $orderService;
        $this->shippingMethodService = $shippingMethodService;
        $this->shippingOptionsService = $shippingOptionService;
        $this->configService = $configService;
        $this->logger = $logger;
        $this->shipmentStatusChangeWebhookWebService = $shipmentStatusChangeWebhookWebService;
        $this->builder = $builder;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced'
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        //Get order
        $order = $event->getOrder();
        //Get order custom fields with key
        if (!empty($order->getCustomFields()[Defaults::MYPARCEL_DELIVERY_OPTIONS_KEY])) {

            //Start the webhook subscriber for updates
            $this->subscribeToWebhook($event->getSalesChannelId());

            $myparcelOptions = $order->getCustomFields()[Defaults::MYPARCEL_DELIVERY_OPTIONS_KEY];

            // Add the order to the shipping options
            $myparcelOptions[ShippingOptionEntity::FIELD_ORDER] = [
                'id' => $order->getId(),
                'versionId' => $order->getVersionId(),
            ];

            //Store shipping options in the database
            $this->shippingOptionsService->createOrUpdateShippingOptions($myparcelOptions, $event->getContext());
            //Update custom fields on the order
            $this->orderService->createOrUpdateOrder([
                'id' => $order->getId(),
                'versionId' => $order->getVersionId(),
                'customFields' => [
                    self::PARAM_MY_PARCEL => json_encode($myparcelOptions),
                ]
            ], $event->getContext());
        }
    }

    /**
     * Subscribes to the webhook
     */
    public function subscribeToWebhook(string $salesChannelId)
    {
        $apiKey = (string)$this->configService->get('MyPaShopware.config.myParcelApiKey', $salesChannelId);
        try {
            $subID = $this->shipmentStatusChangeWebhookWebService->setApiKey($apiKey)
                ->subscribe($this->builder->buildWebhook());
            $this->logger->debug('Hooked to myparcel', [
                'Hook id' => $subID
            ]);
        } catch (AccountNotActiveException|MissingFieldException|ApiException $e) {
            $this->logger->error('Error subscribing to webhook', ['error' => $e]);
        }

    }
}
