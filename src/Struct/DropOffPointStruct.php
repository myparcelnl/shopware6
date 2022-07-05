<?php

namespace MyPa\Shopware\Struct;

use MyParcelNL\Sdk\src\Model\Consignment\DropOffPoint;
use phpDocumentor\Reflection\Types\This;
use Shopware\Core\Framework\Struct\Struct;

class DropOffPointStruct extends Struct
{
    protected ?string $boxNumber;
    protected string $cc;
    protected string $city;
    protected string $locationCode;
    protected string $locationName;
    protected string $street;
    protected int $number;
    protected ?string $numberSuffix;
    protected string $postalCode;
    protected ?string $region;
    protected ?string $retailNetworkId;
    protected ?string $state;

    public function setWithDropOffPoint(DropOffPoint $dropOffPoint)
    {
        $this->setBoxNumber($dropOffPoint->getBoxNumber());
        $this->setCc($dropOffPoint->getCc());
        $this->setCity($dropOffPoint->getCity());
        $this->setLocationCode($dropOffPoint->getLocationCode());
        $this->setLocationName($dropOffPoint->getLocationName());
        $this->setStreet($dropOffPoint->getStreet());
        $this->setNumber($dropOffPoint->getNumber());
        $this->setNumberSuffix($dropOffPoint->getNumberSuffix());
        $this->setPostalCode($dropOffPoint->getPostalCode());
        $this->setRegion($dropOffPoint->getRegion());
        $this->setRetailNetworkId($dropOffPoint->getRetailNetworkId());
        $this->setState($dropOffPoint->getState());
    }

    public function getDropOffPoint():DropOffPoint
    {
        return (new DropOffPoint())
            ->setBoxNumber($this->getBoxNumber())
            ->setCc($this->getCc())
            ->setCity($this->getCity())
            ->setLocationCode($this->getLocationCode())
            ->setLocationName($this->getLocationName())
            ->setStreet($this->getStreet())
            ->setNumber($this->getNumber())
            ->setNumberSuffix($this->getNumberSuffix())
            ->setPostalCode($this->getPostalCode())
            ->setRegion($this->getRegion())
            ->setRetailNetworkId($this->getRetailNetworkId())
            ->setState($this->getState());
    }

    /**
     * @return string|null
     */
    public function getBoxNumber(): ?string
    {
        return $this->boxNumber;
    }

    /**
     * @param string|null $boxNumber
     */
    public function setBoxNumber(?string $boxNumber): void
    {
        $this->boxNumber = $boxNumber;
    }

    /**
     * @return string
     */
    public function getCc(): string
    {
        return $this->cc;
    }

    /**
     * @param string $cc
     */
    public function setCc(string $cc): void
    {
        $this->cc = $cc;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getLocationCode(): string
    {
        return $this->locationCode;
    }

    /**
     * @param string $locationCode
     */
    public function setLocationCode(string $locationCode): void
    {
        $this->locationCode = $locationCode;
    }

    /**
     * @return string
     */
    public function getLocationName(): string
    {
        return $this->locationName;
    }

    /**
     * @param string $locationName
     */
    public function setLocationName(string $locationName): void
    {
        $this->locationName = $locationName;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return string|null
     */
    public function getNumberSuffix(): ?string
    {
        return $this->numberSuffix;
    }

    /**
     * @param string|null $numberSuffix
     */
    public function setNumberSuffix(?string $numberSuffix): void
    {
        $this->numberSuffix = $numberSuffix;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     */
    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string|null
     */
    public function getRetailNetworkId(): ?string
    {
        return $this->retailNetworkId;
    }

    /**
     * @param string|null $retailNetworkId
     */
    public function setRetailNetworkId(?string $retailNetworkId): void
    {
        $this->retailNetworkId = $retailNetworkId;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

}
