<?php

namespace MyPa\Shopware\Controller\Api;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
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
     * @var EntityRepositoryInterface
     */
    private $shipmentsRepository;

    /**
     * @param LoggerInterface           $logger
     * @param EntityRepositoryInterface $shipments
     */
    public function __construct(LoggerInterface $logger, EntityRepositoryInterface $shipments)
    {
        $this->logger = $logger;
        $this->shipmentsRepository = $shipments;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/myparcel/webhook", defaults={"csrf_protected"=false}, name="frontend.myparcel.webhook",
     *                                           options={"seo"="false"}, methods={"GET", "POST"})
     *
     * @param Request             $request
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function webhookCall(Request $request, SalesChannelContext $context): JsonResponse
    {
        $this->logger->debug('Webhook called:', ['request' => $request]);
        $data = $request->get('data');

        if ($data == null) {
            return new JsonResponse(null, 204);
        }

        if (!isset($data['hooks'])) {
            return new JsonResponse(null, 204);
        }

        foreach ($data['hooks'] as $hook) {
            if (!isset($hook['event'])) {
                continue;
            }

            if ($hook['event'] !== 'shipment_status_change') {
                continue;
            }

            if (!isset($hook['shipment_reference_identifier'])) {
                continue;
            }

            if (!isset($hook['status'])) {
                continue;
            }

            //Get shipment based on "consignment_reference"
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('consignmentReference', $hook['shipment_reference_identifier']));
            $shipmentId = $this->shipmentsRepository->searchIds($criteria, $context->getContext())->firstId();

            //Update the order myparcel status with "status"
            $this->shipmentsRepository->update([
                [
                    'id'             => $shipmentId,
                    'shipmentStatus' => $hook['status'],
                ],
            ], $context->getContext());

        }

        return new JsonResponse(null, 204);
    }

}
