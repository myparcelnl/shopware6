<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel;

use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;

class KienerMyParcel extends Plugin
{
    public function activate(ActivateContext $activateContext): void
    {
        /** @var ShippingMethodService $shippingMethodService */
        $shippingMethodService = $this->container->get(ShippingMethodService::class);

        // Install MyParcel shipping methods
        $shippingMethodService->createShippingMethods($activateContext->getContext());
    }
}