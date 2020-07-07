<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel\Core\Content\Shipment;

use Kiener\KienerMyParcel\Core\Content\ShippingOption\ShippingOptionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ShipmentEntity
{
    use EntityIdTrait;

    /**
     * @var int|null
     */
    protected $consignmentId;

    /**
     * @var ShippingOptionEntity
     */
    protected $shippingOption;

    /**
     * @var string
     */
    protected $shippingOptionId;

    /**
     * @var OrderEntity
     */
    protected $order;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $orderVersionId;

    /**
     * @var string|null
     */
    protected $labelUrl;

    /**
     * @return int|null
     */
    public function getConsignmentId(): ?int
    {
        return $this->consignmentId;
    }

    /**
     * @param int|null $consignmentId
     *
     * @return self
     */
    public function setConsignmentId(?int $consignmentId): self
    {
        $this->consignmentId = $consignmentId;
        return $this;
    }

    /**
     * @return ShippingOptionEntity
     */
    public function getShippingOption(): ShippingOptionEntity
    {
        return $this->shippingOption;
    }

    /**
     * @param ShippingOptionEntity $shippingOption
     *
     * @return self
     */
    public function setShippingOption(ShippingOptionEntity $shippingOption): self
    {
        $this->shippingOption = $shippingOption;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingOptionId(): string
    {
        return $this->shippingOptionId;
    }

    /**
     * @param string $shippingOptionId
     *
     * @return self
     */
    public function setShippingOptionId(string $shippingOptionId): self
    {
        $this->shippingOptionId = $shippingOptionId;
        return $this;
    }

    /**
     * @return OrderEntity
     */
    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    /**
     * @param OrderEntity $order
     *
     * @return self
     */
    public function setOrder(OrderEntity $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return self
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderVersionId(): string
    {
        return $this->orderVersionId;
    }

    /**
     * @param string $orderVersionId
     *
     * @return self
     */
    public function setOrderVersionId(string $orderVersionId): self
    {
        $this->orderVersionId = $orderVersionId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabelUrl(): ?string
    {
        return $this->labelUrl;
    }

    /**
     * @param string|null $labelUrl
     *
     * @return self
     */
    public function setLabelUrl(?string $labelUrl): self
    {
        $this->labelUrl = $labelUrl;
        return $this;
    }
}