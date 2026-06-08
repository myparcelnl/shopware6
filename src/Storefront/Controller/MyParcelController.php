<?php

namespace MyPa\Shopware\Storefront\Controller;

use MyPa\Shopware\Service\Consignment\ConsignmentService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

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

    public function __construct(ConsignmentService $consignmentService)
    {
        $this->consignmentService = $consignmentService;
    }

    #[Route('/api/_action/myparcel/carriers', defaults: ['auth_enabled' => true, '_routeScope' => ['api']], name: self::ROUTE_NAME_GET_CARRIERS, methods: ['GET'])]
    public function getCarriers(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS  => true,
            self::RESPONSE_KEY_CARRIERS => $this->consignmentService->getCarrierIds(),
        ]);
    }

    #[Route('/api/_action/myparcel/package_types', defaults: ['auth_enabled' => true, '_routeScope' => ['api']], name: self::ROUTE_NAME_GET_PACKAGE_TYPES, methods: ['GET'])]
    public function getPackageTypes(): JsonResponse
    {
        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS       => true,
            self::RESPONSE_KEY_PACKAGE_TYPES => $this->consignmentService->getPackageTypes(),
        ]);
    }
}
