<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */
namespace Kiener\KienerMyParcel\Storefront\Controller;

use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use MollieShopware\Components\Services\OrderService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannel\SalesChannelContextSwitcher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Kiener\KienerMyParcel\Service\Cookie\CookieProvider;

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
     * @var GenericPageLoader
     */
    private $genericPageLoader;
    private $view;

    /**
     * @var CookieProvider
     */
    private $cookieProvider;

    /**
     * ContextController constructor.
     * @param GenericPageLoader $genericPageLoader
     * @param SalesChannelContextSwitcher $contextSwitcher
     * @param CookieProvider $cookieProvider
     */
    public function __construct(GenericPageLoader $genericPageLoader, SalesChannelContextSwitcher $contextSwitcher, CookieProvider $cookieProvider)
    {
        $this->genericPageLoader = $genericPageLoader;
        $this->contextSwitcher = $contextSwitcher;
        $this->cookieProvider = $cookieProvider;
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
        file_put_contents(__DIR__ . '/export.txt', print_r($data, true));

        /* get vars from post */
        $shippingMethodId = $data->get('shippingMethodId') ?: 0;
        $myparcel_delivery_type= $data->get('myparcel_delivery_type') ?: 0;
        $myparcel_requires_signature= $data->get('myparcel_requires_signature') ?: 0;
        $myparcel_only_recipient= $data->get('myparcel_only_recipient') ?: 0;

        /* set vars to cookie */
        $cookieValue = 'shipid:' . $shippingMethodId;
        $cookieValue .= ',deltype:' . $myparcel_delivery_type;
        $cookieValue .= ',sign:' . $myparcel_requires_signature;
        $cookieValue.= ',recip:' . $myparcel_only_recipient;

        $cookieValue = "[" . trim($cookieValue) . "]";

        $cookies = $this->cookieProvider->getCookieGroups();
        foreach ($cookies as &$cookie) {
            if (!\is_array($cookie)) {
                continue;
            }

            if (!$this->cookieProvider->isRequiredCookieGroup($cookie)) {
                continue;
            }

            if (!\array_key_exists('entries', $cookie)) {
                continue;
            }

            /* find key in array */
            $key = array_search('myparcel-cookie-key', array_column($cookie['entries'], 'cookie'));

            /* set cookie */
            $cookie['entries'][$key] = [
                'snippet_name' => 'cookie.myparcel.name',
                'cookie' => 'myparcel-cookie-key',
                'expiration' => 1,
                'value' => $cookieValue
            ];
        }

        /* debug */
        file_put_contents(__DIR__ . '/export-2.txt', print_r($cookies, true));
        $this->cookieProvider->getCookieGroups($cookies);

        $this->contextSwitcher->update($data, $context);
        return $this->createActionResponse($request);
    }
}
