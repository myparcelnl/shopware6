<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace Kiener\KienerMyParcel\Storefront\Controller;

use Exception;
use Kiener\KienerMyParcel\Service\Consignment\ConsignmentService;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConsignmentController extends StorefrontController
{
    public const ROUTE_NAME_GET_CARRIERS = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES = 'api.action.myparcel.package_types';
    public const ROUTE_NAME_CREATE = 'api.action.myparcel.consignment.create';
    public const ROUTE_NAME_GET_BY_REFERENCE_ID = 'api.action.myparcel.consignment.get_by_reference_id';

    private const RESPONSE_KEY_SUCCESS = 'success';
    private const RESPONSE_KEY_ERROR = 'error';
    private const RESPONSE_KEY_CARRIERS = 'carriers';
    private const RESPONSE_KEY_PACKAGE_TYPES = 'package_types';
    private const RESPONSE_KEY_ORDER_ID = 'order_id';

    private const REQUEST_KEY_CARRIER_ID = 'carrier_id';
    private const REQUEST_KEY_AGE_CHECK = 'age_check';
    private const REQUEST_KEY_LARGE_FORMAT = 'large_format';
    private const REQUEST_KEY_RETURN_IF_NOT_HOME = 'return_if_not_home';
    private const REQUEST_KEY_REQUIRES_SIGNATURE = 'requires_signature';
    private const REQUEST_KEY_ONLY_RECIPIENT = 'only_recipient';
    private const REQUEST_KEY_PACKAGE_TYPE = 'package_type';
    private const REQUEST_KEY_LABEL_POSITIONS = 'label_positions';

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ConsignmentService
     */
    private $consignmentService;

    /**
     * ConsignmentController constructor.
     *
     * @param EntityRepositoryInterface $orderRepository
     * @param ConsignmentService        $consignmentService
     */
    public function __construct(
        EntityRepositoryInterface $orderRepository,
        ConsignmentService $consignmentService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->consignmentService = $consignmentService;
    }

    /**
     * @param string       $orderId
     * @param string|null  $versionId
     * @param Context|null $context
     *
     * @return OrderEntity|null
     */
    private function getOrder(
        string $orderId,
        string $versionId = null,
        Context $context = null
    ): ?OrderEntity
    {
        $criteria = new Criteria([$orderId]);

        if ($versionId !== null) {
            $criteria->addFilter(new EqualsFilter('versionId', $versionId));
        }

        $criteria->addAssociation('lineItems')
            ->addAssociation('orderCustomer')
            ->addAssociation('orderCustomer.salutation')
            ->addAssociation('deliveries')
            ->addAssociation('deliveries.shippingOrderAddress')
            ->addAssociation('deliveries.shippingOrderAddress.country')
            ->addAssociation('deliveries.shippingOrderAddress.salutation');

        return $this->orderRepository
            ->search($criteria, $context ?? Context::createDefaultContext())->get($orderId);
    }

    /**
     * @param Request     $request
     * @param OrderEntity $order
     *
     * @return string|null
     * @throws MissingFieldException
     */
    private function createConsignmentFromRequest(Request $request, OrderEntity $order): ?string
    {
        if ((string)$request->get(self::REQUEST_KEY_CARRIER_ID) === '') {
            return null;
        }

        return $this->consignmentService->createConsignments(
            $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT),
            $order,
            []
        );
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
     *     "/api/v{version}/_action/myparcel/consignment/create",
     *     defaults={"auth_enabled"=true},
     *     name=ConsignmentController::ROUTE_NAME_CREATE,
     *     methods={"POST"}
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        if (
            (string)$request->get(self::RESPONSE_KEY_ORDER_ID) === ''
        ) {
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS => false,
                self::RESPONSE_KEY_ERROR => 'Request is missing a valid order id'
            ]);
        }

        $order = $this->getOrder($request->get(self::RESPONSE_KEY_ORDER_ID));

        if (
            $order === null
        ) {
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS => false,
            ]);
        }

        try {
            $consignmentId = $this->createConsignmentFromRequest($request, $order);
            $success = true;
        } catch (MissingFieldException $e) {
            $success = false;
        }

        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS => $success,
            'id' => $consignmentId ?? null,
        ]);
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/get_by_reference_id/{$referenceId}",
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