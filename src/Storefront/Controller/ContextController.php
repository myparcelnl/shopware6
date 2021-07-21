<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */

namespace MyPa\Shopware\Storefront\Controller;

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
     * ContextController constructor.
     * @param SalesChannelContextSwitcher $contextSwitcher
     */
    public function __construct(SalesChannelContextSwitcher $contextSwitcher)
    {
        $this->contextSwitcher = $contextSwitcher;
    }

    /**
     * @Route("/checkout/configure", name="frontend.checkout.configure", methods={"POST"}, options={"seo"="false"}, defaults={"XmlHttpRequest": true})
     * @param Request $request
     * @param RequestDataBag $data
     * @param SalesChannelContext $context
     * @return Response
     */
    public function configure(Request $request, RequestDataBag $data, SalesChannelContext $context)
    {
        /* get vars from post */
        $shippingMethodId = $data->get('shippingMethodId') ?: 0;
        $myparcel_delivery_date = $data->get('myparcel_delivery_date') ?: 0;
        $myparcel_delivery_type = $data->get('myparcel_delivery_type_'.$myparcel_delivery_date) ?: 0;
        $myparcel_requires_signature= $data->get('myparcel_requires_signature') ?: 0;
        $myparcel_only_recipient= $data->get('myparcel_only_recipient') ?: 0;

        /* set vars to cookie */
        $cookieValue = '' . $shippingMethodId;
        $cookieValue .= '_' . $myparcel_delivery_date;
        $cookieValue .= '_' . $myparcel_delivery_type;
        $cookieValue .= '_' . $myparcel_requires_signature;
        $cookieValue .= '_' . $myparcel_only_recipient;

        $cookieValue = trim($cookieValue);

        /* set cookievalue */
        //setcookie("myparcel-cookie-key", htmlentities($cookieValue), time() + 600, '/');
        setcookie("myparcel-cookie-key", htmlentities($cookieValue), 0, '/');

        $this->contextSwitcher->update($data, $context);

        return $this->createActionResponse($request);
    }
}
