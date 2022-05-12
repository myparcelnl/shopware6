<?php

namespace MyPa\Shopware\Service\ShippingMethod;

use MyPa\Shopware\Service\Shopware\ShippingMethod\ShippingMethodService;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ShippingMethodCreatorService
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function create(ActivateContext $activateContext, ContainerInterface $container,string $path)
    {
        $shippingMethodService = new ShippingMethodService(
            $container->get('delivery_time.repository'),
            $container->get('media.repository'),
            $container->get('rule.repository'),
            $container->get('shipping_method.repository'),
            $this->mediaService,
            $container->get('myparcel.logger')
        );
        $shippingMethodService->createShippingMethods($path, $activateContext->getContext());
    }
}
