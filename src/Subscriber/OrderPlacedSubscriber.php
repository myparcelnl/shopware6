<?php declare(strict_types = 1);

namespace Kiener\KienerMyParcel\Subscriber;

use Kiener\KienerMyParcel\Core\Content\ShippingMethod\ShippingMethodEntity;
use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use Kiener\KienerMyParcel\Service\Order\OrderService;
use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Kiener\KienerMyParcel\Service\ShippingOptions\ShippingOptionsService;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderPlacedSubscriber implements EventSubscriberInterface
{
    private const PARAM_MY_PARCEL = 'my_parcel';
    private const PARAM_DELIVERY_TYPE = 'delivery_type';
    private const PARAM_REQUIRES_AGE_CHECK = 'requires_age_check';
    private const PARAM_REQUIRES_SIGNATURE = 'requires_signature';
    private const PARAM_ONLY_RECIPIENT = 'only_recipient';
    private const PARAM_RETURN_IF_NOT_HOME = 'return_if_not_home';
    private const PARAM_LARGE_FORMAT = 'large_format';
    private const PARAM_SHIPPING_METHOD_ID = 'shipping_method_id';

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
     * Creates a new instance of the order placed subscriber.
     *
     * @param RequestStack              $requestStack
     * @param OrderService              $orderService
     * @param ShippingMethodService     $shippingMethodService
     * @param ShippingOptionsService    $shippingOptionService
     */
    public function __construct(
        RequestStack $requestStack,
        OrderService $orderService,
        ShippingMethodService $shippingMethodService,
        ShippingOptionsService $shippingOptionService
    )
    {
       $this->requestStack = $requestStack;
       $this->orderService = $orderService;
       $this->shippingMethodService = $shippingMethodService;
       $this->shippingOptionsService = $shippingOptionService;
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
            if (isset($params[self::PARAM_DELIVERY_TYPE])) {
                $options[ShippingOptionEntity::FIELD_DELIVERY_TYPE] = (int) $params[self::PARAM_DELIVERY_TYPE];
            }

            if (isset($params[self::PARAM_REQUIRES_AGE_CHECK])) {
                $options[ShippingOptionEntity::FIELD_REQUIRES_AGE_CHECK] = (bool) $params[self::PARAM_REQUIRES_AGE_CHECK];
            }

            if (isset($params[self::PARAM_REQUIRES_SIGNATURE])) {
                $options[ShippingOptionEntity::FIELD_REQUIRES_SIGNATURE] = (bool) $params[self::PARAM_REQUIRES_SIGNATURE];
            }

            if (isset($params[self::PARAM_ONLY_RECIPIENT])) {
                $options[ShippingOptionEntity::FIELD_ONLY_RECIPIENT] = (bool) $params[self::PARAM_ONLY_RECIPIENT];
            }

            if (isset($params[self::PARAM_RETURN_IF_NOT_HOME])) {
                $options[ShippingOptionEntity::FIELD_RETURN_IF_NOT_HOME] = (bool) $params[self::PARAM_RETURN_IF_NOT_HOME];
            }

            if (isset($params[self::PARAM_LARGE_FORMAT])) {
                $options[ShippingOptionEntity::FIELD_LARGE_FORMAT] = (bool) $params[self::PARAM_LARGE_FORMAT];
            }

            if (
                isset($params[self::PARAM_SHIPPING_METHOD_ID])
                && strlen($params[self::PARAM_SHIPPING_METHOD_ID]) >= 32
            ) {
                $shippingMethod = $this->shippingMethodService->getShippingMethodByShopwareShippingMethodId(
                    $params[self::PARAM_SHIPPING_METHOD_ID],
                    new Context(new SystemSource())
                );
            }
        }

        if (
            !empty($options)
            && $shippingMethod !== null
        ) {
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
                    self::PARAM_MY_PARCEL => $options,
                ]
            ], $event->getContext());
        }
    }
}