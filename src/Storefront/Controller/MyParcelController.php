<?php

namespace MyPa\Shopware\Storefront\Controller;

use Exception;
use MyPa\Shopware\Facade\MyParcelFacade;
use MyPa\Shopware\Service\Consignment\ConsignmentService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MyParcelController extends StorefrontController
{
    public const ROUTE_NAME_GET_CARRIERS      = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES = 'api.action.myparcel.package_types';

    private const RESPONSE_KEY_SUCCESS       = 'success';
    private const RESPONSE_KEY_CARRIERS      = 'carriers';
    private const RESPONSE_KEY_PACKAGE_TYPES = 'package_types';

    /**
     * @var ConsignmentService
     */
    private $consignmentService;

	/**
	 * @var MyParcelFacade
	 */
	private $myParcelFacade;

	/**
     * MyParcelController constructor.
     *
     * @param ConsignmentService $consignmentService
     * @param MyParcelFacade     $myParcelFacade
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
     * @Route(
     *     "/api/_action/myparcel/carriers",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
     *     name=MyParcelController::ROUTE_NAME_GET_CARRIERS,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getCarriers(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS  => true,
            self::RESPONSE_KEY_CARRIERS => $this->consignmentService->getCarrierIds(),
        ]);
    }

    /**
     * @Route(
     *     "/api/_action/myparcel/package_types",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
     *     name=MyParcelController::ROUTE_NAME_GET_PACKAGE_TYPES,
     *     methods={"GET"}
     *     )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getPackageTypes(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS       => true,
            self::RESPONSE_KEY_PACKAGE_TYPES => $this->consignmentService->getPackageTypes(),
        ]);
    }
}
