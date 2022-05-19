<?php

namespace MyPa\Shopware\Controller\Api;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends StorefrontController
{

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @var EntityRepository
     */
    private $orders;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/myparcel/webhook", defaults={"csrf_protected"=false}, name="frontend.myparcel.webhook",
     *                                           options={"seo"="false"}, methods={"GET", "POST"})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function webhookCall(Request $request,SalesChannelContext $context): JsonResponse
    {
        $this->logger->debug('Webhook called:',['request'=>$request]);
//$apiKey = (string)$this->configService->get('MyPaShopware.config.myParcelApiKey', $salesChannelId);
        //Get order based on "shipment_id"
        //Update the order myparcel status with "status"

        return new JsonResponse(null,204);
    }

}
