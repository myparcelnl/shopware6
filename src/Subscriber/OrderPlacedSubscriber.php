<?php declare(strict_types=1);

namespace MyPa\Shopware\Subscriber;

use MyPa\Shopware\Core\Content\ShippingMethod\ShippingMethodEntity;
use MyPa\Shopware\Core\Content\ShippingOption\ShippingOptionEntity;
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

    /**
     * Creates a shipping option record in the database.
     *
     * @param CheckoutOrderPlacedEvent $event
     */
    public function onOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        /** @var array $params */
        $params = [];

        /** @var array $options */
        $options = [];

        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        /** @var ShippingMethodEntity|null $shippingMethod */
        $shippingMethod = null;

        // Get the parameters from the request
        if ($request !== null) {
            $params = $request->get('myparcel');
        }

        // Add the options from the checkout to the array of options
        if (is_array($params) && !empty($params)) {

            // Check if we have a MyParcel shippingMethod
            if (
                isset($params[self::PARAM_SHIPPING_METHOD_ID])
                && strlen($params[self::PARAM_SHIPPING_METHOD_ID]) >= 32
            ) {
                $shippingMethod = $this->shippingMethodService->getShippingMethodByShopwareShippingMethodId(
                    $params[self::PARAM_SHIPPING_METHOD_ID],
                    new Context(new SystemSource())
                );
            }

            if ($shippingMethod !== null) {

                if (isset($params[self::PARAM_DELIVERY_TYPE])) {
                    $options[ShippingOptionEntity::FIELD_DELIVERY_TYPE] = (int)$params[self::PARAM_DELIVERY_TYPE];
                } else {
                    $options[ShippingOptionEntity::FIELD_DELIVERY_TYPE] = (int)$this->configService->get('MyPaShopware.config.myParcelDefaultDeliveryWindow');
                }

                if (isset($params[self::PARAM_DELIVERY_DATE]) && !empty($params[self::PARAM_DELIVERY_DATE])) {
                    $strTime = \strtotime($params[self::PARAM_DELIVERY_DATE]);
                    if (!$strTime) {
                        $strTime = strtotime("+1 day");
                    }
                    $options[ShippingOptionEntity::FIELD_DELIVERY_DATE] = \date('Y-m-d', $strTime);
                } else {
                    $options[ShippingOptionEntity::FIELD_DELIVERY_DATE] = \date('Y-m-d', strtotime("+1 day"));
                }

                if (isset($params[self::PARAM_REQUIRES_AGE_CHECK])) {
                    $options[ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK] = (bool)$params[self::PARAM_REQUIRES_AGE_CHECK];
                } else {
                    $options[ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK] = (bool)$this->configService->get('MyPaShopware.config.myParcelDefaultAgeCheck');
                }

                if (isset($params[self::PARAM_REQUIRES_SIGNATURE])) {
                    $options[ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE] = (bool)$params[self::PARAM_REQUIRES_SIGNATURE];
                } else {
                    $options[ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE] = (bool)$this->configService->get('MyPaShopware.config.myParcelDefaultSignature');
                }

                if (isset($params[self::PARAM_ONLY_RECIPIENT])) {
                    $options[ShippingOptionEntity::FIELD_ONLY_RECIPIENT] = (bool)$params[self::PARAM_ONLY_RECIPIENT];
                } else {
                    $options[ShippingOptionEntity::FIELD_ONLY_RECIPIENT] = (bool)$this->configService->get('MyPaShopware.config.myParcelDefaultOnlyRecipient');
                }

                if (isset($params[self::PARAM_RETURN_IF_NOT_HOME])) {
                    $options[ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME] = (bool)$params[self::PARAM_RETURN_IF_NOT_HOME];
                } else {
                    $options[ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME] = (bool)$this->configService->get('MyPaShopware.config.myParcelDefaultReturnNotHome');
                }

                if (isset($params[self::PARAM_LARGE_FORMAT])) {
                    $options[ShippingOptionEntity::FIELD_LARGE_FORMAT] = (bool)$params[self::PARAM_LARGE_FORMAT];
                } else {
                    $options[ShippingOptionEntity::FIELD_LARGE_FORMAT] = (bool)$this->configService->get('MyPaShopware.config.myParcelDefaultLargeFormat');
                }

                if (isset($params[self::PARAM_PACKAGE_TYPE])) {
                    $options[ShippingOptionEntity::FIELD_PACKAGE_TYPE] = (bool)$params[self::PARAM_LARGE_FORMAT];
                } else {
                    $options[ShippingOptionEntity::FIELD_PACKAGE_TYPE] = (int)$this->configService->get('MyPaShopware.config.myParcelDefaultPackageType');
                }

                if (isset($params[self::PARAM_DELIVERY_LOCATION]) && $params[self::PARAM_DELIVERY_LOCATION] == 'pickup') {
                    $decodedPickupPointData = json_decode(base64_decode($params[self::PARAM_PICKUP_DATA]), true);

                    $options[ShippingOptionEntity::FIELD_PICKUP_LOCATION_ID] = intval($decodedPickupPointData['location_code']);
                    $options[ShippingOptionEntity::FIELD_PICKUP_NAME] = $decodedPickupPointData['location'];
                    $options[ShippingOptionEntity::FIELD_PICKUP_STREET] = $decodedPickupPointData['street'];
                    $options[ShippingOptionEntity::FIELD_PICKUP_NUMBER] = $decodedPickupPointData['number'];
                    $options[ShippingOptionEntity::FIELD_PICKUP_POSTAL_CODE] = $decodedPickupPointData['postal_code'];
                    $options[ShippingOptionEntity::FIELD_PICKUP_CITY] = $decodedPickupPointData['city'];
                    $options[ShippingOptionEntity::FIELD_PICKUP_CC] = $decodedPickupPointData['cc'];
                    $options[ShippingOptionEntity::FIELD_RETAIL_NETWORK_ID] = $decodedPickupPointData['retail_network_id'];
                }

                if (!empty($options)) {
                    //Start the webhook subscriber for updates
                    $this->subscribeToWebhook($event->getSalesChannelId());

                    // Add the order to the shipping options
                    $options[ShippingOptionEntity::FIELD_ORDER] = [
                        'id' => $event->getOrder()->getId(),
                        'versionId' => $event->getOrder()->getVersionId(),
                    ];

                    // Add the carrier id to the shipping options
                    $options[ShippingOptionEntity::FIELD_CARRIER_ID] = $shippingMethod->getCarrierId();

                    // Store shipping options in the database
                    $this->shippingOptionsService->createOrUpdateShippingOptions($options, new Context(new SystemSource()));

                    // Update custom fields on the order
                    $this->orderService->createOrUpdateOrder([
                        'id' => $event->getOrder()->getId(),
                        'versionId' => $event->getOrder()->getVersionId(),
                        'customFields' => [
                            self::PARAM_MY_PARCEL => json_encode($options),
                        ]
                    ], $event->getContext());

                    setcookie("myparcel-cookie-key", '', 600, '/');
                }
            }
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
