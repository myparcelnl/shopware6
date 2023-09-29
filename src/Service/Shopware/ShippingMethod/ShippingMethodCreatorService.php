<?php

namespace MyPa\Shopware\Service\Shopware\ShippingMethod;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;


class ShippingMethodCreatorService
{
    private MediaService $mediaService;
    private EntityRepository $deliveryTimeRepository;
    private EntityRepository $mediaRepository;
    private EntityRepository $ruleRepository;
    private EntityRepository $shippingMethodRepository;
    private LoggerInterface $logger;

    /**
     * @param MediaService              $mediaService
     * @param EntityRepository $deliveryTimeRepository
     * @param EntityRepository $mediaRepository
     * @param EntityRepository $ruleRepository
     * @param EntityRepository $shippingMethodRepository
     * @param LoggerInterface           $logger
     */
    public function __construct(
        MediaService              $mediaService,
        EntityRepository $deliveryTimeRepository,
        EntityRepository $mediaRepository,
        EntityRepository $ruleRepository,
        EntityRepository $shippingMethodRepository,
        LoggerInterface           $logger
    )
    {
        $this->mediaService = $mediaService;
        $this->deliveryTimeRepository = $deliveryTimeRepository;
        $this->mediaRepository = $mediaRepository;
        $this->ruleRepository = $ruleRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->logger = $logger;
    }


    public function create(string $path, Context $context)
    {
        $shippingMethodService = new ShippingMethodService(
            $this->deliveryTimeRepository,
            $this->mediaRepository,
            $this->ruleRepository,
            $this->shippingMethodRepository,
            $this->mediaService,
            $this->logger
        );
        $shippingMethodService->createShippingMethods($path, $context);
    }
}
