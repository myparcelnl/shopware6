<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace Kiener\KienerMyParcel\Storefront\Controller;

use Kiener\KienerMyParcel\Service\Order\OrderService;
use Kiener\KienerMyParcel\Service\Shipment\ShipmentService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ShipmentController extends StorefrontController
{
    public const ROUTE_NAME_GET_SHIPMENTS = 'api.action.myparcel.shipments';

    private const RESPONSE_KEY_SUCCESS = 'success';
    private const RESPONSE_KEY_ERROR = 'error';
    private const RESPONSE_KEY_SHIPMENTS = 'shipments';

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * ConsignmentController constructor.
     *
     * @param OrderService      $orderService
     * @param ShipmentService   $shipmentService
     */
    public function __construct(
        OrderService $orderService,
        ShipmentService $shipmentService
    )
    {
        $this->orderService = $orderService;
        $this->shipmentService = $shipmentService;
    }
    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/shipments",
     *     defaults={"auth_enabled"=true},
     *     name=ShipmentController::ROUTE_NAME_GET_SHIPMENTS,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     */
    public function getShipments(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            self::RESPONSE_KEY_SHIPMENTS => $this->shipmentService->getShipments(new Context(new SystemSource()))
        ]);
    }
}