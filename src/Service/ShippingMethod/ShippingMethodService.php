<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel\Service\ShippingMethod;

use Kiener\KienerMyParcel\Service\Consignment\ConsignmentService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Rule\CartAmountRule;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Uuid\Uuid;

class ShippingMethodService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsignmentService
     */
    private $consignmentService;

    /**
     * @var EntityRepositoryInterface
     */
    private $myParcelShippingMethodRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $shopwareShippingMethodRepository;

    public function __construct(
        LoggerInterface $logger,
        ConsignmentService $consignmentService,
        EntityRepositoryInterface $myParcelShippingMethodRepository,
        EntityRepositoryInterface $shopwareShippingMethodRepository
    )
    {
        $this->logger = $logger;
        $this->consignmentService = $consignmentService;
        $this->myParcelShippingMethodRepository = $myParcelShippingMethodRepository;
        $this->shopwareShippingMethodRepository = $shopwareShippingMethodRepository;
    }

    public function createShippingMethods(): void
    {
        /** @var array $carriers */
        $carriers = $this->consignmentService->getCarrierIds();

        if (
            is_array($carriers)
            && !empty($carriers)
        ) {
            foreach ($carriers as $carrierName => $carrierId) {
                $shippingMethodId = $this->createShopwareShippingMethod($carrierId, $carrierName);

            }
        }
    }

    private function createMyParcelShippingMethod(
        string $shippingMethodId,
        string $carrierId,
        string $carrierName,
        Context $context
    ): bool
    {
        /** @var string $id */
        $id = Uuid::randomBytes();

        /** @var EntityWrittenEvent $event */
        $event = $this->myParcelShippingMethodRepository->upsert([
            [
                'id' => $id,
                'carrierId' => $carrierId,
                'carrierName' => $carrierName,
                'shippingMethodId' => $shippingMethodId,
            ]
        ], $context);

        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(',', $event->getErrors()),
                $event->getErrors()
            );

            return false;
        }

        return true;
    }

    private function createShopwareShippingMethod(
        int $carrierId,
        string $carrierName,
        Context $context
    ): ?string
    {
        /** @var string $id */
        $id = Uuid::randomBytes();

        $event = $this->shopwareShippingMethodRepository->upsert([
            [
                'id' => $id,
                'name' => $carrierName,
                'active' => false,
                'availabilityRule' => [
                    'id' => null,
                    'name' => 'Cart >= 0',
                    'priority' => 1,
                    'type' => (new CartAmountRule())->getName(),
                ],
                'deliveryTimeId' => '',
            ]
        ], $context);

        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(',', $event->getErrors()),
                $event->getErrors()
            );

            return null;
        }

        return $id;
    }
}