<?php

namespace MyPa\Shopware\Service\ShippingMethod;

use MyPa\Shopware\Service\Shopware\ShippingMethod\ShippingMethodService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ShippingMethodCreatorService
{
    private MediaService $mediaService;
    private EntityRepositoryInterface $deliveryTimeRepository;
    private EntityRepositoryInterface $mediaRepository;
    private EntityRepositoryInterface $ruleRepository;
    private EntityRepositoryInterface $shippingMethodRepository;
    private LoggerInterface $logger;

    /**
     * @param MediaService $mediaService
     * @param EntityRepositoryInterface $deliveryTimeRepository
     * @param EntityRepositoryInterface $mediaRepository
     * @param EntityRepositoryInterface $ruleRepository
     * @param EntityRepositoryInterface $shippingMethodRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        MediaService $mediaService,
        EntityRepositoryInterface $deliveryTimeRepository,
        EntityRepositoryInterface $mediaRepository,
        EntityRepositoryInterface $ruleRepository,
        EntityRepositoryInterface $shippingMethodRepository,
        LoggerInterface $logger
    )
    {
        $this->mediaService = $mediaService;
        $this->deliveryTimeRepository = $deliveryTimeRepository;
        $this->mediaRepository = $mediaRepository;
        $this->ruleRepository = $ruleRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->logger = $logger;
    }


    public function create(ActivateContext $activateContext, ContainerInterface $container,string $path)
    {
        $shippingMethodService = new ShippingMethodService(
            $this->deliveryTimeRepository,
            $this->mediaRepository,
            $this->ruleRepository,
            $this->shippingMethodRepository,
            $this->mediaService,
            $this->logger
        );
        $shippingMethodService->createShippingMethods($path, $activateContext->getContext());
    }
}
