<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace MyPa\Shopware\Storefront\Controller;

use Exception;
use MyPa\Shopware\Facade\MyParcelFacade;
use MyPa\Shopware\Service\Consignment\ConsignmentService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MyParcelController extends StorefrontController
{
    public const ROUTE_NAME_GET_CARRIERS = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES = 'api.action.myparcel.package_types';

    /* For backwards compatibility with 6.3*/
    public const ROUTE_NAME_GET_CARRIERS_LEGACY = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES_LEGACY = 'api.action.myparcel.package_types';
    /* End backwards compatibility*/

    private const RESPONSE_KEY_SUCCESS = 'success';
    private const RESPONSE_KEY_CARRIERS = 'carriers';
    private const RESPONSE_KEY_PACKAGE_TYPES = 'package_types';

    /**
     * @var ConsignmentService
     */
    private $consignmentService;


    /**
     * MyParcelController constructor.
     *
     * @param ConsignmentService $consignmentService
     */
    public function __construct(
        ConsignmentService $consignmentService,
        MyParcelFacade     $myParcelFacade
    )
    {
        $this->consignmentService = $consignmentService;
        $this->myParcelFacade = $myParcelFacade;
    }



    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/_action/myparcel/carriers",
     *     defaults={"auth_enabled"=true},
     *     name=MyParcelController::ROUTE_NAME_GET_CARRIERS,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getCarriers(): JsonResponse
    {
        return $this->getCarriersResponse();
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/carriers",
     *     defaults={"auth_enabled"=true},
     *     name=MyParcelController::ROUTE_NAME_GET_CARRIERS_LEGACY,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getCarriersLegacy(): JsonResponse
    {
        return $this->getCarriersResponse();
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    private function getCarriersResponse(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            self::RESPONSE_KEY_CARRIERS => $this->consignmentService->getCarrierIds(),

        ]);
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/_action/myparcel/package_types",
     *     defaults={"auth_enabled"=true},
     *     name=MyParcelController::ROUTE_NAME_GET_PACKAGE_TYPES,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getPackageTypes(): JsonResponse
    {
        return $this->getPackageTypesResponse();
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/package_types",
     *     defaults={"auth_enabled"=true},
     *     name=MyParcelController::ROUTE_NAME_GET_PACKAGE_TYPES_LEGACY,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getPackageTypesLegacy(): JsonResponse
    {
        return $this->getPackageTypesResponse();
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    private function getPackageTypesResponse(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => true,
            self::RESPONSE_KEY_PACKAGE_TYPES => $this->consignmentService->getPackageTypes(),

        ]);
    }
}
