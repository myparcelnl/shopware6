<?php

namespace MyPa\Shopware\Storefront\Controller;

use MyPa\Shopware\Service\Config\ConfigReader;
use MyPa\Shopware\Service\Shopware\CartService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class CartController extends AbstractController
{
    protected CartService $cartService;
    protected LoggerInterface $logger;
    protected ConfigReader $configReader;

    /**
     * @param CartService $cartService
     * @param LoggerInterface $logger
     * @param ConfigReader $configReader
     */
    public function __construct(CartService $cartService, LoggerInterface $logger, ConfigReader $configReader)
    {
        $this->cartService = $cartService;
        $this->logger = $logger;
        $this->configReader = $configReader;
    }


    /**
     * @Route("/widget/checkout/myparcel/add-to-cart", name="frontend.checkout.myparcel.add-to-cart", options={"seo"=false}, methods={"POST"}, defaults={"XmlHttpRequest"=true, "csrf_protected"=true})
     *
     * @param RequestDataBag $data
     * @param SalesChannelContext $context
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addDataToCart(RequestDataBag $data, SalesChannelContext $context)
    {
        $myParcelData = $data->get('myparcel');
        if (($myParcelData !== null)) {
            $this->cartService->addData([
                'myparcel' => ['deliveryData' => json_decode($myParcelData)],
            ], $context);

            $calculatedCard = $this->cartService->recalculate($context);
            $html = $this->render('@Storefront/storefront/page/checkout/summary.html.twig', ['page' => ['cart' => $calculatedCard]]);


            return $this->json($html, 200);
        } else {
            $this->logger->warning("No deliverData found", ['data' => $data]);
            return $this->json("No delivery data found", 500);
        }


    }
}
