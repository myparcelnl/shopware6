<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel\Service\ShippingMethod;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class ShippingMethodService
{
    public function __construct(
        LoggerInterface $logger,
        EntityRepositoryInterface $shippingMethodRepository
    )
    {

    }
}