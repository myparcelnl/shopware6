<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */
namespace Kiener\KienerMyParcel\Storefront\Controller;

use Exception;
use Kiener\KienerMyParcel\Core\Content\Shipment\ShipmentEntity;
use Kiener\KienerMyParcel\Service\Consignment\ConsignmentService;
use Kiener\KienerMyParcel\Service\Shipment\ShipmentService;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\PlatformRequest;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConsignmentController extends StorefrontController
{
    public const ROUTE_NAME_GET_CARRIERS = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES = 'api.action.myparcel.package_types';
    public const ROUTE_NAME_CREATE = 'api.action.myparcel.consignment.create';
    public const ROUTE_NAME_CREATE_CONSIGNMENTS = 'api.action.myparcel.consignment.create_consignments';
    public const ROUTE_NAME_GET_BY_REFERENCE_ID = 'api.action.myparcel.consignment.get_by_reference_id';

    private const REQUEST_KEY_ORDER_IDS = 'order_ids';
    private const REQUEST_KEY_LABEL_POSITIONS = 'label_positions';
    private const REQUEST_KEY_SHIPMENT_ID = 'shipment_id';

    private const RESPONSE_KEY_SUCCESS = 'success';
    private const RESPONSE_KEY_ERROR = 'error';
    private const RESPONSE_KEY_CARRIERS = 'carriers';
    private const RESPONSE_KEY_PACKAGE_TYPES = 'package_types';

    /**
     * @var ConsignmentService
     */
    private $consignmentService;

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * ConsignmentController constructor.
     *
     * @param ConsignmentService $consignmentService
     * @param ShipmentService    $shipmentService
     */
    public function __construct(
        ConsignmentService $consignmentService,
        ShipmentService $shipmentService
    )
    {
        $this->consignmentService = $consignmentService;
        $this->shipmentService = $shipmentService;
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/carriers",
     *     defaults={"auth_enabled"=true},
     *     name=ConsignmentController::ROUTE_NAME_GET_CARRIERS,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getCarriers(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            self::RESPONSE_KEY_CARRIERS => $this->consignmentService->getCarrierIds(),

        ]);
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/package_types",
     *     defaults={"auth_enabled"=true},
     *     name=ConsignmentController::ROUTE_NAME_GET_PACKAGE_TYPES,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getPackageTypes(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            self::RESPONSE_KEY_PACKAGE_TYPES => $this->consignmentService->getPackageTypes(),

        ]);
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/consignment/create-consignments",
     *     defaults={"auth_enabled"=true},
     *     name=ConsignmentController::ROUTE_NAME_CREATE_CONSIGNMENTS,
     *     methods={"POST"}
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function createConsignments(Request $request): JsonResponse
    {
        $context = new Context(new SystemSource());

        if (
            $request->get(self::REQUEST_KEY_ORDER_IDS) === null
            || !is_array($request->get(self::REQUEST_KEY_ORDER_IDS))
            || empty($request->get(self::REQUEST_KEY_ORDER_IDS))
        ) {
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS => false,
                self::RESPONSE_KEY_ERROR => sprintf('Missing valid %s array with ids as parameter', self::REQUEST_KEY_ORDER_IDS)
            ]);
        }

        if (
            $request->get(self::REQUEST_KEY_LABEL_POSITIONS) === null
            || !is_array($request->get(self::REQUEST_KEY_LABEL_POSITIONS))
            || empty($request->get(self::REQUEST_KEY_LABEL_POSITIONS))
        ) {
            $labelPositions = $request->get(self::REQUEST_KEY_LABEL_POSITIONS);
        }

        $consignments = $this->consignmentService->createConsignments(
            $context,
            $request->get(self::REQUEST_KEY_ORDER_IDS),
            $labelPositions ?? null
        );

        if (
            (string)$request->get(self::REQUEST_KEY_SHIPMENT_ID) === ''
        ) {
            $shipment = $this->shipmentService->getShipment($request->get(self::REQUEST_KEY_SHIPMENT_ID), $context);

            if ($shipment !== null)
            {
                $shipmentParameters = [
                    ShipmentEntity::FIELD_ID => $shipment->getId(),
                    ShipmentEntity::FIELD_LABEL_URL => $consignments->getLinkOfLabels()
                ];

                $this->shipmentService->createOrUpdateShipment($shipmentParameters, $context);
            }

        }

        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => $consignments !== null,
        ]);
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/get-by-reference-id/{$referenceId}",
     *     defaults={"auth_enabled"=true},
     *     name=ConsignmentController::ROUTE_NAME_GET_BY_REFERENCE_ID,
     *     methods={"POST"}
     *     )
     *
     * @param string $referenceId
     *
     * @return JsonResponse
     * @throws MissingFieldException
     */
    public function getByReferenceId(string $referenceId): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            'consignments' => $this->consignmentService->findByReferenceId($referenceId),
        ]);
    }
}