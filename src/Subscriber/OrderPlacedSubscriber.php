<?php declare(strict_types = 1);

namespace Kiener\KienerMyParcel\Subscriber;

use Kiener\KienerMyParcel\Core\Content\ShippingMethod\ShippingMethodEntity;
use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Kiener\KienerMyParcel\Service\ShippingOption\ShippingOptionService;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderPlacedSubscriber implements EventSubscriberInterface
{
    private const PARAM_REQUIRES_AGE_CHECK = 'requires_age_check';
    private const PARAM_REQUIRES_SIGNATURE = 'requires_age_signature';
    private const PARAM_ONLY_RECIPIENT = 'only_recipient';
    private const PARAM_RETURN_IF_NOT_HOME = 'return_if_not_home';
    private const PARAM_LARGE_FORMAT = 'large_format';
    private const PARAM_SHIPPING_METHOD_ID = 'shipping_method_id';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /**
     * @var ShippingOptionService
     */
    private $shippingOptionService;

    /**
     * Creates a new instance of the order placed subscriber.
     *
     * @param RequestStack          $requestStack
     * @param ShippingMethodService $shippingMethodService
     * @param ShippingOptionService $shippingOptionService
     */
    public function __construct(
        RequestStack $requestStack,
        ShippingMethodService $shippingMethodService,
        ShippingOptionService $shippingOptionService
    )
    {
       $this->requestStack = $requestStack;
       $this->shippingMethodService = $shippingMethodService;
       $this->shippingOptionService = $shippingOptionService;
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

        if ($request !== null) {
            $params = $request->get('myparcel');
        }

        if (is_array($params) && !empty($params)) {
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

            if (isset($params[self::PARAM_SHIPPING_METHOD_ID])) {
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

            $options[ShippingOptionEntity::FIELD_ORDER] = [
                'id' => $event->getOrder()->getId(),
                'versionId' => $event->getOrder()->getVersionId(),
            ];

            $options[ShippingOptionEntity::FIELD_CARRIER_ID] = $shippingMethod->getCarrierId();

            $this->shippingOptionService->createOrUpdateShippingOptions($options, new Context(new SystemSource()));
        }
    }
}