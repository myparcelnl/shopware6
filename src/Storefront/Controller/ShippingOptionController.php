<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace Kiener\KienerMyParcel\Storefront\Controller;

use Kiener\KienerMyParcel\Service\Order\OrderService;
use Kiener\KienerMyParcel\Service\ShippingOption\ShippingOptionService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ShippingOptionController extends StorefrontController
{
    public const ROUTE_NAME_GET_SHIPPING_OPTIONS = 'api.action.myparcel.shippingOptions';

    private const RESPONSE_KEY_SUCCESS = 'success';
    private const RESPONSE_KEY_ERROR = 'error';
    private const RESPONSE_KEY_SHIPPING_OPTIONS = 'shippingOptions';

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var ShippingOptionService
     */
    private $shippingOptionService;

    /**
     * ConsignmentController constructor.
     *
     * @param OrderService            $orderService
     * @param ShippingOptionService   $shippingOptionService
     */
    public function __construct(
        OrderService $orderService,
        ShippingOptionService $shippingOptionService
    )
    {
        $this->orderService = $orderService;
        $this->shippingOptionService = $shippingOptionService;
    }
    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/shipping-options",
     *     defaults={"auth_enabled"=true},
     *     name=ShippingOptionController::ROUTE_NAME_GET_SHIPPING_OPTIONS,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     */
    public function getShippingOptions(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            self::RESPONSE_KEY_SHIPPING_OPTIONS => $this->shippingOptionService->getAllShippingOptions(new Context(new SystemSource()))
        ]);
    }
}