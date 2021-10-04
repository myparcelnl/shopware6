<?php

namespace MyPa\Shopware\Storefront\Controller;

use MyPa\Shopware\Helper\AddressHelper;
use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use Shopware\Core\Framework\Context;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

use Shopware\Core\Defaults;

class DeliveryOptionsController extends StorefrontController
{
    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /** @var SystemConfigService */
    private $configService;

    private const RESPONSE_KEY_SUCCESS = 'success';
    private const RESPONSE_KEY_ERROR = 'error';
    private const RESPONSE_KEY_CODE = 'code';
    private const RESPONSE_KEY_DELIVERY_OPTIONS = 'delivery_options';

    public function __construct(
        LoggerInterface $logger,
        ShippingMethodService $shippingMethodService,
        SystemConfigService $configService
    )
    {
        $this->shippingMethodService = $shippingMethodService;
        $this->configService = $configService;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route(
     *     "/myparcel/delivery_options",
     *     name="myparcel.delivery_options",
     *     methods={"POST|GET"},
     *     defaults={"csrf_protected"=false, "XmlHttpRequest"=true}
     *     )
     *
     * @return Response
     * @throws Exception
     */
    public function getDeliveryOptions(Request $request, SalesChannelContext $salesChannelContext, Context $context): Response
    {

        /** @var string $carrier_id */
        $carrier_id = (($request->get('method') != null) ? $request->get('method') : $salesChannelContext->getShippingMethod()->getId());

        /** @var string $cc */
        $cc = $salesChannelContext->getShippingLocation()->getCountry()->getIso();

        /** @var string $postal_code */
        $postal_code = $salesChannelContext->getShippingLocation()->getAddress()->getZipcode();

        $address = $salesChannelContext->getShippingLocation()->getAddress();
        $config = $this->configService->get('MyPaShopware.config');

        $parsedAddress = AddressHelper::parseAddress($address, $config);

        /** @var string $number */
        $number = $parsedAddress['houseNumber'];

        /** @var ShippingMethodEntity $carrier */
        $carrier = $this->shippingMethodService->getShippingMethodByShopwareShippingMethodId($carrier_id, $context);

        if(!$carrier){
            return new JsonResponse([
                self::RESPONSE_KEY_SUCCESS => false,
                self::RESPONSE_KEY_ERROR => 'No MyParcel carrier for this Shopware carrier',
                self::RESPONSE_KEY_CODE => '422'
            ]);
        }

        $response = file_get_contents('https://api.myparcel.nl/delivery_options?platform=myparcel&carrier='.$carrier->getCarrierName().'&cc='.$cc.'&number='.$number.'&postal_code='.$postal_code);

        if(!$response || empty(json_decode($response)->data->delivery)){

            if(json_decode($response)->errors){
                return new JsonResponse([
                    self::RESPONSE_KEY_SUCCESS => false,
                    self::RESPONSE_KEY_ERROR => json_decode($response, true)['errors'],
                    self::RESPONSE_KEY_CODE => '503',
                ]);
            } else {
                return new JsonResponse([
                    self::RESPONSE_KEY_SUCCESS => false,
                    self::RESPONSE_KEY_ERROR => 'Failed to receive options from MyParcel',
                    self::RESPONSE_KEY_CODE => '503',
                ]);
            }
        }
        $myparcelCookieData = $request->cookies->get('myparcel-cookie-key');

        $cookieArray = explode('_', $myparcelCookieData);


        if($cookieArray[0] == "pickup"){
            $location_id = $cookieArray[6];
            $delivery_location_type = "pickup";
        }else{
            $location_id = null;
            $delivery_location_type = "address";
        }

        $data = json_decode($response, true)['data'];
        $viewData = [
            'options' => (($data['delivery'])?:null),
            'pickupPoints' => (($data['pickup'])?:null),
            'carrier_id' => $salesChannelContext->getShippingMethod()->getId(),
            'carrier' => $carrier,
            'salesContext' => $salesChannelContext,
            'context' => $context,
            'config' => $config,
            'delivery_location_type' => $delivery_location_type,
            'location_id' => $location_id
        ];

        return $this->renderStorefront('@Storefront/storefront/component/checkout/carrier-options.html.twig', $viewData);


    }
}
