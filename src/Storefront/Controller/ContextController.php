<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace MyPa\Shopware\Storefront\Controller;

use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannel\SalesChannelContextSwitcher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class ContextController extends StorefrontController
{
    /**
     * @var SalesChannelContextSwitcher
     */
    private $contextSwitcher;

    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /**
     * ContextController constructor.
     * @param SalesChannelContextSwitcher $contextSwitcher
     * @param ShippingMethodService $shippingMethodService
     */
    public function __construct(SalesChannelContextSwitcher $contextSwitcher, ShippingMethodService $shippingMethodService)
    {
        $this->contextSwitcher = $contextSwitcher;
        $this->shippingMethodService = $shippingMethodService;
    }

    /**
     * @Route("/checkout/configure", name="frontend.checkout.configure", methods={"POST"}, options={"seo"="false"}, defaults={"XmlHttpRequest": true})
     * @param Request $request
     * @param RequestDataBag $data
     * @param SalesChannelContext $salesChannelContext
     * @param Context $content
     * @return Response
     */
    public function configure(Request $request, RequestDataBag $data, SalesChannelContext $salesChannelContext, Context $content)
    {
        $shippingMethodId = $data->get('shippingMethodId') ?: 0;

        $response = $this->createActionResponse($request);

        if(!$shippingMethodId){
            return $response;
        }

        $shippingMethod = $this->shippingMethodService->getShopwareShippingMethodById($shippingMethodId, $content);

        if(!$shippingMethod){
            return $response;
        }

        //check if shipping method is not myparcel then return;
        if(!$this->shippingMethodService->isMyParcelShippingMethod($shippingMethod, $content)){
            return $response;
        }

        $deliveryLocation = $data->get('delivery_location', 'address');

        /* get vars from post */
        switch($deliveryLocation) {
            case "address":
            default:
                $myparcel_delivery_location_type = $deliveryLocation;
                $myparcel_delivery_date = $data->get('myparcel_delivery_date') ?: 0;
                $myparcel_delivery_type = $data->get('myparcel_delivery_type_' . $myparcel_delivery_date) ?: 0;
                $myparcel_requires_signature = $data->get('myparcel_requires_signature') ?: 0;
                $myparcel_only_recipient = $data->get('myparcel_only_recipient') ?: 0;

                /* set vars to cookie */
                $cookieValue = trim(implode('_', [
                    $myparcel_delivery_location_type,
                    $shippingMethodId,
                    $myparcel_delivery_date,
                    $myparcel_delivery_type,
                    $myparcel_requires_signature,
                    $myparcel_only_recipient,
                ]));
                break;
            case 'pickup':
                $myparcel_delivery_location_type = $deliveryLocation;
                $myparcel_delivery_type = 4;
                $myparcel_requires_signature = 1;
                $myparcel_pickup_point_id = $data->get('pickup_point');
                $myparcel_delivery_date = $data->get('myparcel_delivery_date_pickup_'.$myparcel_pickup_point_id) ?: 0;
                $myparcel_pickupPointData = $data->get('pickup_point_data_' . $myparcel_pickup_point_id) ? \base64_encode( $data->get('pickup_point_data_' . $myparcel_pickup_point_id)): 0;
                $myparcel_only_recipient = 0;

                /* set vars to cookie */
                $cookieValue = trim(implode('_', [
                    $myparcel_delivery_location_type,
                    $shippingMethodId,
                    $myparcel_delivery_date,
                    $myparcel_delivery_type,
                    $myparcel_requires_signature,
                    $myparcel_only_recipient,
                    $myparcel_pickup_point_id,
                    $myparcel_pickupPointData
                ]));

                break;
        }

        $this->contextSwitcher->update($data, $salesChannelContext);

        if(empty($cookieValue)){
            return $response;
        }

        /* set cookie */
        $cookie = new Cookie("myparcel-cookie-key", htmlentities($cookieValue), 0, '/');
        $response->headers->setCookie($cookie);

        return $response;
    }
}
