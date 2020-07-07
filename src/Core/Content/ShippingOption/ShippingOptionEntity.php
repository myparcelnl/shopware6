<?php

namespace Kiener\KienerMyParcel\Core\Content\ShippingOption;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ShippingOptionEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var int
     */
    protected $carrierId;

    /**
     * @var int
     */
    protected $packageType;

    /**
     * @var int
     */
    protected $requiresAgeCheck;

    /**
     * @var int
     */
    protected $requiresSignature;

    /**
     * @var int
     */
    protected $onlyRecipient;

    /**
     * @var int
     */
    protected $returnIfNotHome;

    /**
     * @return int
     */
    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    /**
     * @param int $carrierId
     *
     * @return ShippingOptionEntity
     */
    public function setCarrierId(int $carrierId): ShippingOptionEntity
    {
        $this->carrierId = $carrierId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPackageType(): int
    {
        return $this->packageType;
    }

    /**
     * @param int $packageType
     *
     * @return ShippingOptionEntity
     */
    public function setPackageType(int $packageType): ShippingOptionEntity
    {
        $this->packageType = $packageType;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequiresAgeCheck(): int
    {
        return $this->requiresAgeCheck;
    }

    /**
     * @param int $requiresAgeCheck
     *
     * @return ShippingOptionEntity
     */
    public function setRequiresAgeCheck(int $requiresAgeCheck): ShippingOptionEntity
    {
        $this->requiresAgeCheck = $requiresAgeCheck;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequiresSignature(): int
    {
        return $this->requiresSignature;
    }

    /**
     * @param int $requiresSignature
     *
     * @return ShippingOptionEntity
     */
    public function setRequiresSignature(int $requiresSignature): ShippingOptionEntity
    {
        $this->requiresSignature = $requiresSignature;
        return $this;
    }

    /**
     * @return int
     */
    public function getOnlyRecipient(): int
    {
        return $this->onlyRecipient;
    }

    /**
     * @param int $onlyRecipient
     *
     * @return ShippingOptionEntity
     */
    public function setOnlyRecipient(int $onlyRecipient): ShippingOptionEntity
    {
        $this->onlyRecipient = $onlyRecipient;
        return $this;
    }

    /**
     * @return int
     */
    public function getReturnIfNotHome(): int
    {
        return $this->returnIfNotHome;
    }

    /**
     * @param int $returnIfNotHome
     *
     * @return ShippingOptionEntity
     */
    public function setReturnIfNotHome(int $returnIfNotHome): ShippingOptionEntity
    {
        $this->returnIfNotHome = $returnIfNotHome;
        return $this;
    }

}