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
     * ContextController constructor.
     * @param GenericPageLoader $genericPageLoader
     * @param SalesChannelContextSwitcher $contextSwitcher
     */
    public function __construct(GenericPageLoader $genericPageLoader, SalesChannelContextSwitcher $contextSwitcher)
    {
        $this->genericPageLoader = $genericPageLoader;
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
        file_put_contents(__DIR__ . '/export.txt', print_r($data, true));
//        [shippingMethodId] => bd08a080903c4c938edb9835e9c5d4b7
//        [myparcel_delivery_type] => 2
//        [myparcel_requires_signature] => 1

        $this->contextSwitcher->update($data, $context);
//        var_dump($request);
//        die();
//        $request->view->assign('myparcel-post-data', $data);
        return $this->createActionResponse($request);
    }

//    /**
//     * @Route("/checkout/confirm", name="frontend.checkout.confirm.page", options={"seo"="false"}, methods={"GET"}, defaults={"XmlHttpRequest"=true})
//     * @param Request $request
//     * @param SalesChannelContext $context
//     * @return RedirectResponse|Response
//     */
//    public function confirm(Request $request, SalesChannelContext $context)
//    {
////        [shippingMethodId] => bd08a080903c4c938edb9835e9c5d4b7
////        [myparcel_delivery_type] => 2
////        [myparcel_requires_signature] => 1
//
////        var_dump($request);
////        die();
//
//        if (!$context->getCustomer()) {
//            return $this->redirectToRoute('frontend.checkout.register.page');
//        }
//
//        if ($this->cartService->getCart($context->getToken(), $context)->getLineItems()->count() === 0) {
//            return $this->redirectToRoute('frontend.checkout.cart.page');
//        }
//
//        $page = $this->confirmPageLoader->load($request, $context);
//
//        return $this->renderStorefront('@Storefront/storefront/page/checkout/confirm/index.html.twig', ['page' => $page]);
//    }
}
