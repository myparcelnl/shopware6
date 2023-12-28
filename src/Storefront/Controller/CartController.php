<?php

namespace MyPa\Shopware\Storefront\Controller;

use MyPa\Shopware\Service\Config\ConfigGenerator;
use MyPa\Shopware\Service\Shopware\CartService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class CartController extends AbstractController
{
    protected CartService $cartService;
    protected LoggerInterface $logger;
    protected ConfigGenerator $configReader;

    /**
     * @param CartService $cartService
     * @param LoggerInterface $logger
     * @param ConfigGenerator $configReader
     */
    public function __construct(CartService $cartService, LoggerInterface $logger, ConfigGenerator $configReader)
    {
        $this->cartService = $cartService;
        $this->logger = $logger;
        $this->configReader = $configReader;
    }

    /**
     * @Route("/widget/checkout/myparcel/add-to-cart", name="frontend.checkout.myparcel.add-to-cart", options={"seo"=false}, methods={"POST"}, defaults={"XmlHttpRequest"=true, "csrf_protected"=false})
     *
     * @param RequestDataBag $data
     * @param SalesChannelContext $context
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addDataToCart(RequestDataBag $data, SalesChannelContext $context)
    {
        $myParcelData = $data->get('myparcel');

        if (null !== $myParcelData) {
            $this->cartService->addData([
                'myparcel' => ['deliveryData' => json_decode($myParcelData)],
            ], $context);

            $calculatedCard = $this->cartService->recalculate($context);
            $html = $this->render('@Storefront/storefront/page/checkout/summary.html.twig', ['page' => ['cart' => $calculatedCard]]);

            return $this->json($html, 200);
        } else {
            $this->logger->warning('No deliverData found', ['data' => $data]);

            return $this->json('No delivery data found', 500);
        }
    }

    /**
     * @Route("/widget/checkout/myparcel/set-package-type", name="frontend.checkout.myparcel.set-package-type", options={"seo"=false}, methods={"POST"}, defaults={"XmlHttpRequest"=true, "csrf_protected"=false})
     *
     * @param RequestDataBag $data
     * @param SalesChannelContext $context
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function setPackageType(RequestDataBag $data, SalesChannelContext $context)
    {
        $packageType = $data->get(CartService::PACKAGE_TYPE_REQUEST_KEY);
        $this->cartService->addData([CartService::PACKAGE_TYPE_CART_DATA_KEY=>$packageType], $context);

        return $this->json([CartService::PACKAGE_TYPE_REQUEST_KEY=>$packageType], 200);
    }
}
