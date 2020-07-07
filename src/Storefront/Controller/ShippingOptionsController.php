<?php


namespace Kiener\KienerMyParcel\Storefront\Controller;


use Exception;
use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ShippingOptionsController extends StorefrontController
{
    public const ROUTE_NAME_CREATE = 'api.action.myparcel.shipping_options.create';

    public const REQUEST_KEY_ORDER_ID = 'order_id';
    public const REQUEST_KEY_CARRIER_ID = 'carrier_id';
    public const REQUEST_KEY_AGE_CHECK = 'age_check';
    public const REQUEST_KEY_LARGE_FORMAT = 'large_format';
    public const REQUEST_KEY_RETURN_IF_NOT_HOME = 'return_if_not_home';
    public const REQUEST_KEY_REQUIRES_SIGNATURE = 'requires_signature';
    public const REQUEST_KEY_ONLY_RECIPIENT = 'only_recipient';
    public const REQUEST_KEY_PACKAGE_TYPE = 'package_type';

    /**
     * @RouteScope(scopes={"api"})
     * @Route(
     *     "/api/v{version}/_action/myparcel/shipping_options/create",
     *     defaults={"auth_enabled"=true},
     *     name=ShippingOptionsController::ROUTE_NAME_CREATE,
     *     methods={"POST"}
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function createForOrder(Request $request): ?ShippingOptionEntity
    {
        if ((string)$request->get(self::REQUEST_KEY_ORDER_ID) === '') {
            return null;
        }

        $shippingOptions = new ShippingOptionEntity();

        if ((string)$request->get(self::REQUEST_KEY_AGE_CHECK) !== '') {
            $shippingOptions->setRequiresAgeCheck($request->get(self::REQUEST_KEY_AGE_CHECK));
        }

        if ((string)$request->get(self::REQUEST_KEY_LARGE_FORMAT) !== '') {
            $shippingOptions->setLargeFormat($request->get(self::REQUEST_KEY_LARGE_FORMAT));
        }

        if ((string)$request->get(self::REQUEST_KEY_RETURN_IF_NOT_HOME) !== '') {
            $shippingOptions->setReturnIfNotHome($request->get(self::REQUEST_KEY_RETURN_IF_NOT_HOME));
        }

        if ((string)$request->get(self::REQUEST_KEY_REQUIRES_SIGNATURE) !== '') {
            $shippingOptions->setRequiresSignature($request->get(self::REQUEST_KEY_REQUIRES_SIGNATURE));
        }

        if ((string)$request->get(self::REQUEST_KEY_ONLY_RECIPIENT) !== '') {
            $shippingOptions->setOnlyRecipient($request->get(self::REQUEST_KEY_ONLY_RECIPIENT));
        }

        $carrierId = $request->get(self::REQUEST_KEY_CARRIER_ID);

        if ((string)$carrierId !== ''
            && is_int($carrierId)
            && in_array($carrierId, [
                BpostConsignment::CARRIER_ID,
                DPDConsignment::CARRIER_ID,
                PostNLConsignment::CARRIER_ID,
            ], true)
        ) {
            $shippingOptions->setCarrierId($carrierId);
        }

        $packageType = $request->get(self::REQUEST_KEY_PACKAGE_TYPE);

        if ((string)$packageType !== ''
            && is_int($packageType)
            && in_array($packageType, AbstractConsignment::PACKAGE_TYPES_IDS, true)
        ) {
            $shippingOptions->setPackageType($packageType);
        }

    }
}