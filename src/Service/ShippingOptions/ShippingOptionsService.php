<?php


namespace Kiener\KienerMyParcel\Service\ShippingOptions;


use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ShippingOptionsService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityRepositoryInterface
     */
    private $shippingOptionsRepository;

    /**
     * ShippingOptionsService constructor.
     *
     * @param LoggerInterface           $logger
     * @param EntityRepositoryInterface $shippingOptionsRepository
     */
    public function __construct(
        LoggerInterface $logger,
        EntityRepositoryInterface $shippingOptionsRepository
    )
    {
        $this->logger = $logger;
        $this->shippingOptionsRepository = $shippingOptionsRepository;
    }

    /**
     * @param OrderEntity $orderEntity
     * @param Context     $context
     *
     * @return ShippingOptionEntity|null
     */
    public function getShippingOptionsForOrder(OrderEntity $orderEntity, Context $context): ?ShippingOptionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $orderEntity->getId()))
            ->addFilter(new EqualsFilter('orderVersionId', $orderEntity->getVersionId()));

        return $this->shippingOptionsRepository->search($criteria, $context)->first();
    }
}