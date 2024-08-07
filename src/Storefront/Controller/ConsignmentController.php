<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace MyPa\Shopware\Storefront\Controller;

use Exception;
use MyPa\Shopware\Exception\Config\ConfigFieldValueMissingException;
use MyPa\Shopware\Service\Consignment\ConsignmentService;
use MyPa\Shopware\Service\Shipment\ShipmentService;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Helper\TrackTraceUrl;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConsignmentController extends StorefrontController
{
    public const ROUTE_NAME_GET_CARRIERS            = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES       = 'api.action.myparcel.package-types';
    public const ROUTE_NAME_CREATE                  = 'api.action.myparcel.consignment.create';
    public const ROUTE_NAME_CREATE_CONSIGNMENTS     = 'api.action.myparcel.consignment.create-consignments';
    public const ROUTE_NAME_GET_BY_REFERENCE_ID     = 'api.action.myparcel.consignment.get-by-reference-id';
    public const ROUTE_NAME_GET_FOR_SHIPPING_OPTION = 'api.action.myparcel.consignment.get-for-shipping-option';
    public const ROUTE_NAME_DOWNLOAD_LABELS         = 'api.action.myparcel.consignment.download-labels';
    public const ROUTE_NAME_TRACK_AND_TRACE         = 'api.action.myparcel.consignment.track-and-trace';

    /* For backwards compatibility with 6.3*/
    public const ROUTE_NAME_GET_CARRIERS_LEGACY            = 'api.action.myparcel.carriers';
    public const ROUTE_NAME_GET_PACKAGE_TYPES_LEGACY       = 'api.action.myparcel.package_types';
    public const ROUTE_NAME_CREATE_LEGACY                  = 'api.action.myparcel.consignment.create';
    public const ROUTE_NAME_CREATE_CONSIGNMENTS_LEGACY     = 'api.action.myparcel.consignment.create_consignments';
    public const ROUTE_NAME_GET_BY_REFERENCE_ID_LEGACY     = 'api.action.myparcel.consignment.get_by_reference_id';
    public const ROUTE_NAME_GET_FOR_SHIPPING_OPTION_LEGACY = 'api.action.myparcel.consignment.get_for_shipping_option';
    public const ROUTE_NAME_DOWNLOAD_LABELS_LEGACY         = 'api.action.myparcel.consignment.download_labels';
    public const ROUTE_NAME_TRACK_AND_TRACE_LEGACY         = 'api.action.myparcel.consignment.track_and_trace';
    /* End backwards compatibility*/

    private const REQUEST_KEY_ORDERS             = 'orders';
    private const REQUEST_KEY_LABEL_POSITIONS    = 'label_positions';
    private const REQUEST_KEY_PRINT_SMALL_LABEL  = 'print_small_label';
    private const REQUEST_KEY_PACKAGE_TYPE       = 'package_type';
    private const REQUEST_KEY_NUMBER_OF_LABELS   = 'number_of_labels';
    private const REQUEST_KEY_SHIPMENT_ID        = 'shipment_id';
    private const REQUEST_KEY_REFERENCE_ID       = 'reference_id';
    private const REQUEST_KEY_REFERENCE_IDS      = 'reference_ids';
    private const REQUEST_KEY_SHIPPING_OPTION_ID = 'shipping_option_id';

    private const RESPONSE_KEY_CONSIGNMENTS     = 'consignments';
    private const RESPONSE_KEY_ERROR            = 'error';
    private const RESPONSE_KEY_LABEL_URL        = 'labelUrl';
    private const RESPONSE_KEY_TRACK_TRACE_INFO = 'trackTraceInfo';
    private const RESPONSE_KEY_SUCCESS          = 'success';
    private const RESPONSE_KEY_TRANSLATION      = 'translation';

    /**
     * @var ConsignmentService
     */
    private $consignmentService;

    /**
     * @var ShipmentService
     */
    private $shipmentService;


    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * ConsignmentController constructor.
     *
     * @param ConsignmentService  $consignmentService
     * @param ShipmentService     $shipmentService
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(
        ConsignmentService  $consignmentService,
        ShipmentService     $shipmentService,
        SystemConfigService $systemConfigService
    )
    {
        $this->consignmentService = $consignmentService;
        $this->shipmentService = $shipmentService;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @Route(
     *     "/api/_action/myparcel/consignment/get-for-shipping-option",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
     *     name=ConsignmentController::ROUTE_NAME_GET_FOR_SHIPPING_OPTION,
     *     methods={"POST"}
     *     )
     *
     * @param RequestDataBag $request
     * @return JsonResponse
     */
    public function getForShippingOption(RequestDataBag $request): JsonResponse
    {
        $shippingOptionId = $request->get(self::REQUEST_KEY_SHIPPING_OPTION_ID);

        if (empty($shippingOptionId)) {
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS => false,
                self::RESPONSE_KEY_ERROR   => sprintf(
                    'Request is missing a valid %s',
                    self::REQUEST_KEY_SHIPPING_OPTION_ID
                ),
            ]);
        }

        $consignments = $this->shipmentService->getShipmentsByShippingOptionId(
            $shippingOptionId,
            new Context(new SystemSource())
        );

        if (! $consignments->getElements()) {
            $order = $this->consignmentService->getFullOrderByShippingOptionId($shippingOptionId);

            if (! $order) {
                return new JsonResponse([
                    self::RESPONSE_KEY_SUCCESS => false,
                    self::RESPONSE_KEY_ERROR   => 'Not found',
                ]);
            }

            try {
                $this->consignmentService->createConsignment(
                    new Context(new SystemSource()),
                    $order,
                    null
                );
            } catch (\Throwable $e) {
                return new JsonResponse([
                    self::RESPONSE_KEY_SUCCESS => false,
                    self::RESPONSE_KEY_ERROR   => $e->getMessage(),
                ]);
            }
        }

        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS      => true,
            self::RESPONSE_KEY_CONSIGNMENTS => $consignments->getElements(),
        ]);
    }

    /**
     * @Route(
     *     "/api/_action/myparcel/consignment/create-consignments",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
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
        /**
         * @todo Per consignment een Shipment maken zodat dit niet in Javascript hoeft
         * Voor Shipment wordt meegegeven: Order_id (al present), Order_version_id (al present) en ShippingOption_ID (required) [Optioneel: InsuredAmount]
         */

        $context = new Context(new SystemSource());

        if (
            $request->get(self::REQUEST_KEY_ORDERS) === null
            || !is_array($request->get(self::REQUEST_KEY_ORDERS))
            || empty($request->get(self::REQUEST_KEY_ORDERS))
        ) {
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS => false,
                self::RESPONSE_KEY_ERROR   => sprintf('Missing valid %s array with ids as parameter', self::REQUEST_KEY_ORDERS),
            ]);
        }

        if (
            $request->get(self::REQUEST_KEY_LABEL_POSITIONS) !== null
            && is_array($request->get(self::REQUEST_KEY_LABEL_POSITIONS))
            && !empty($request->get(self::REQUEST_KEY_LABEL_POSITIONS))
        ) {
            $labelPositions = $request->get(self::REQUEST_KEY_LABEL_POSITIONS);
        } else if ('A6' === $this->systemConfigService->get('MyPaShopware.config.myParcelDefaultLabelFormat')
            || 1 === $request->get(self::REQUEST_KEY_PRINT_SMALL_LABEL)
        ) {
            $labelPositions = null;
        } else {
            $labelPositions = [1];
        }

        if (
            $request->get(self::REQUEST_KEY_PACKAGE_TYPE) !== null
            && !empty($request->get(self::REQUEST_KEY_PACKAGE_TYPE))
        ) {
            $packageType = $request->get(self::REQUEST_KEY_PACKAGE_TYPE);
        }

        if (
            $request->get(self::REQUEST_KEY_NUMBER_OF_LABELS) !== null
            && !empty($request->get(self::REQUEST_KEY_NUMBER_OF_LABELS))
        ) {
            $numberOfLabels = $request->get(self::REQUEST_KEY_NUMBER_OF_LABELS);
        }

        try {
            $consignments = $this->consignmentService->createConsignments(
                $context,
                $request->get(self::REQUEST_KEY_ORDERS),
                $labelPositions ?? null,
                $packageType ?? null,
                $numberOfLabels ?? null
            );
        }
        catch (ConfigFieldValueMissingException $exception) {
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS     => false,
                self::RESPONSE_KEY_ERROR       => $exception->getMessage(),
                self::RESPONSE_KEY_TRANSLATION => 'ConfigFieldValueMissingException',
            ]);
        }


        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS   => $consignments->isNotEmpty(),
            self::RESPONSE_KEY_LABEL_URL => $consignments->getLinkOfLabels(),
        ]);
    }

    /**
     * @Route(
     *     "/api/_action/myparcel/consignment/download-labels",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
     *     name=ConsignmentController::ROUTE_NAME_DOWNLOAD_LABELS,
     *     methods={"POST"}
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws MissingFieldException
     */
    public function downloadLabels(Request $request): JsonResponse
    {
        if (
            $request->get(self::REQUEST_KEY_LABEL_POSITIONS) !== null
            && is_array($request->get(self::REQUEST_KEY_LABEL_POSITIONS))
            && !empty($request->get(self::REQUEST_KEY_LABEL_POSITIONS))
        ) {
            $labelPositions = $request->get(self::REQUEST_KEY_LABEL_POSITIONS);
        }

        if (
            $request->get(self::REQUEST_KEY_REFERENCE_IDS) !== null
            && is_array($request->get(self::REQUEST_KEY_REFERENCE_IDS))
            && !empty($request->get(self::REQUEST_KEY_REFERENCE_IDS))
        ) {
            $referenceIds = $request->get(self::REQUEST_KEY_REFERENCE_IDS);
        }

        if (
            isset($referenceIds)
            && is_array($referenceIds)
            && !empty($referenceIds)
        ) {
            $consignments = $this->consignmentService->findManyByReferenceId($referenceIds);
        }

        if (isset($consignments)) {
            try {
                if (
                    isset($labelPositions)
                    && is_array($labelPositions)
                    && !empty($labelPositions)
                ) {
                    $consignments->setLinkOfLabels(count($labelPositions) === 1 ? $labelPositions[0] : $labelPositions);
                } else {
                    $consignments->setLinkOfLabels(false);
                }
            }
            catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }

        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS   => isset($consignments),
            self::RESPONSE_KEY_LABEL_URL => isset($consignments) ? $consignments->getLinkOfLabels() : null,
        ]);
    }

    /**
     * @Route(
     *     "/api/_action/myparcel/consignment/track-and-trace",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
     *     name=ConsignmentController::ROUTE_NAME_TRACK_AND_TRACE,
     *     methods={"POST"}
     *     )
     *
     * @param Request $request
     * @return JsonResponse
     * @throws MissingFieldException
     */
    public function trackAndTrace(Request $request): JsonResponse
    {
        if (
            $request->get(self::REQUEST_KEY_REFERENCE_ID) !== null
        ) {
            $referenceId = $request->get(self::REQUEST_KEY_REFERENCE_ID);
        }

        if (isset($referenceId)) {
            /** @var array $consignment */
            $consignment = $this->consignmentService->findByReferenceId($referenceId);
        }

        if (
            isset($consignment)
            && is_array($consignment)
            && !empty($consignment)
        ) {
            $trackTraceInfo = [
                'barcode' => $consignment[0]->getBarcode(),
                'url'     => TrackTraceUrl::create(
                    $consignment[0]->getBarcode(),
                    $consignment[0]->getPostalCode(),
                    $consignment[0]->getCountry()
                ),
                //'consignment' => $consignment,
            ];
        }

        return new JsonResponse([
            self::RESPONSE_KEY_SUCCESS          => isset($consignment),
            self::RESPONSE_KEY_TRACK_TRACE_INFO => $trackTraceInfo ?? null,
        ]);
    }

    /**
     * @Route(
     *     "/api/_action/myparcel/consignment/get-by-reference-id/{$referenceId}",
     *     defaults={"auth_enabled"=true,"_routeScope"={"api"}},
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
            'consignments'             => $this->consignmentService->findByReferenceId($referenceId),
        ]);
    }
}
