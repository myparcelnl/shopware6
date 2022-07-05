<?php

namespace MyPa\Shopware\Struct;

use MyPa\Shopware\Service\Attribute\AttributeStruct;

class ConfigStruct extends AttributeStruct
{
    //General settings
    protected string $myParcelApiKey = '';
    protected string $instaboxDropOff = '';
    protected string $addressFieldsConfiguration = '{street}';
    protected string $platform = 'myparcel';
    protected string $packageType = 'package';

    // General NPM settings
    protected bool $allowShowDeliveryDate = true;
    protected bool $allowMorningDelivery = true;
    protected bool $priceMorningDeliveryEnabled = false;
    protected float $priceMorningDelivery;
    protected bool $priceStandardDeliveryEnabled = false;
    protected float $priceStandardDelivery;
    protected bool $priceSameDayDeliveryEnabled = false;
    protected float $priceSameDayDelivery;
    protected bool $allowEveningDelivery = true;
    protected bool $priceEveningDeliveryEnabled = false;
    protected float $priceEveningDelivery;
    protected bool $priceSignatureEnabled = false;
    protected float $priceSignature;
    protected bool $priceOnlyRecipientEnabled = false;
    protected float $priceOnlyRecipient;
    protected bool $pricePickupEnabled = false;
    protected float $pricePickup;
    protected bool $allowSaturdayDelivery = true;
    protected bool $allowMondayDelivery = true;
    protected bool $allowPickupLocations = true;
    protected bool $allowSignature = true;
    protected bool $allowOnlyRecipient = true;
    protected string $dropOffDays;
    protected bool $cutoffTimeEnabled = false;
    protected string $cutoffTime;
    protected bool $deliveryDaysWindowEnabled;
    protected int $deliveryDaysWindow = 7;
    protected bool $dropOffDelayEnabled = false;
    protected int $dropOffDelay = 0;

    //Per carrier settings
    //PostNL
    private bool $enabledPostNL = true; //Can't be disabled (setter removed)
    protected bool $allowShowDeliveryDatePostNL = true;
    protected bool $allowMorningDeliveryPostNL = true;
    protected bool $priceMorningDeliveryEnabledPostNL = true;
    protected float $priceMorningDeliveryPostNL;
    protected bool $priceStandardDeliveryEnabledPostNL = false;
    protected float $priceStandardDeliveryPostNL;
    protected bool $priceSameDayDeliveryEnabledPostNL = false;
    protected float $priceSameDayDeliveryPostNL;
    protected bool $allowEveningDeliveryPostNL = true;
    protected bool $priceEveningDeliveryEnabledPostNL = false;
    protected float $priceEveningDeliveryPostNL;
    protected bool $priceSignatureEnabledPostNL = false;
    protected float $priceSignaturePostNL;
    protected bool $priceOnlyRecipientEnabledPostNL=false;
    protected float $priceOnlyRecipientPostNL;
    protected bool $pricePickupEnabledPostNL = false;
    protected float $pricePickupPostNL;
    protected bool $allowSaturdayDeliveryPostNL = true;
    protected bool $allowMondayDeliveryPostNL = true;
    protected bool $allowPickupLocationsPostNL = true;
    protected bool $allowSignaturePostNL = true;
    protected bool $allowOnlyRecipientPostNL = true;
    protected string $dropOffDaysPostNL;
    protected bool $cutoffTimeEnabledPostNL=false;
    protected string $cutoffTimePostNL;
    protected bool $deliveryDaysWindowEnabledPostNL;
    protected int $deliveryDaysWindowPostNL;
    protected bool $dropOffDelayEnabledPostNL;
    protected int $dropOffDelayPostNL;

    /**
     * @return string
     */
    public function getMyParcelApiKey(): string
    {
        return $this->myParcelApiKey;
    }

    /**
     * @param string $myParcelApiKey
     */
    public function setMyParcelApiKey(string $myParcelApiKey): void
    {
        $this->myParcelApiKey = $myParcelApiKey;
    }

    /**
     * @return string
     */
    public function getInstaboxDropOff(): string
    {
        return $this->instaboxDropOff;
    }

    /**
     * @param string $instaboxDropOff
     */
    public function setInstaboxDropOff(string $instaboxDropOff): void
    {
        $this->instaboxDropOff = $instaboxDropOff;
    }

    /**
     * @return string
     */
    public function getAddressFieldsConfiguration(): string
    {
        return $this->addressFieldsConfiguration;
    }

    /**
     * @param string $addressFieldsConfiguration
     */
    public function setAddressFieldsConfiguration(string $addressFieldsConfiguration): void
    {
        $this->addressFieldsConfiguration = $addressFieldsConfiguration;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }

    /**
     * @return string
     */
    public function getPackageType(): string
    {
        return $this->packageType;
    }

    /**
     * @param string $packageType
     */
    public function setPackageType(string $packageType): void
    {
        $this->packageType = $packageType;
    }

    /**
     * @return bool
     */
    public function isAllowShowDeliveryDate(): bool
    {
        return $this->allowShowDeliveryDate;
    }

    /**
     * @param bool $allowShowDeliveryDate
     */
    public function setAllowShowDeliveryDate(bool $allowShowDeliveryDate): void
    {
        $this->allowShowDeliveryDate = $allowShowDeliveryDate;
    }

    /**
     * @return bool
     */
    public function isAllowMorningDelivery(): bool
    {
        return $this->allowMorningDelivery;
    }

    /**
     * @param bool $allowMorningDelivery
     */
    public function setAllowMorningDelivery(bool $allowMorningDelivery): void
    {
        $this->allowMorningDelivery = $allowMorningDelivery;
    }

    /**
     * @return bool
     */
    public function isPriceMorningDeliveryEnabled(): bool
    {
        return $this->priceMorningDeliveryEnabled;
    }

    /**
     * @param bool $priceMorningDeliveryEnabled
     */
    public function setPriceMorningDeliveryEnabled(bool $priceMorningDeliveryEnabled): void
    {
        $this->priceMorningDeliveryEnabled = $priceMorningDeliveryEnabled;
    }

    /**
     * @return float
     */
    public function getPriceMorningDelivery(): float
    {
        return $this->priceMorningDelivery;
    }

    /**
     * @param float $priceMorningDelivery
     */
    public function setPriceMorningDelivery(float $priceMorningDelivery): void
    {
        $this->priceMorningDelivery = $priceMorningDelivery;
    }

    /**
     * @return bool
     */
    public function isPriceStandardDeliveryEnabled(): bool
    {
        return $this->priceStandardDeliveryEnabled;
    }

    /**
     * @param bool $priceStandardDeliveryEnabled
     */
    public function setPriceStandardDeliveryEnabled(bool $priceStandardDeliveryEnabled): void
    {
        $this->priceStandardDeliveryEnabled = $priceStandardDeliveryEnabled;
    }

    /**
     * @return float
     */
    public function getPriceStandardDelivery(): float
    {
        return $this->priceStandardDelivery;
    }

    /**
     * @param float $priceStandardDelivery
     */
    public function setPriceStandardDelivery(float $priceStandardDelivery): void
    {
        $this->priceStandardDelivery = $priceStandardDelivery;
    }

    /**
     * @return bool
     */
    public function isPriceSameDayDeliveryEnabled(): bool
    {
        return $this->priceSameDayDeliveryEnabled;
    }

    /**
     * @param bool $priceSameDayDeliveryEnabled
     */
    public function setPriceSameDayDeliveryEnabled(bool $priceSameDayDeliveryEnabled): void
    {
        $this->priceSameDayDeliveryEnabled = $priceSameDayDeliveryEnabled;
    }

    /**
     * @return float
     */
    public function getPriceSameDayDelivery(): float
    {
        return $this->priceSameDayDelivery;
    }

    /**
     * @param float $priceSameDayDelivery
     */
    public function setPriceSameDayDelivery(float $priceSameDayDelivery): void
    {
        $this->priceSameDayDelivery = $priceSameDayDelivery;
    }

    /**
     * @return bool
     */
    public function isAllowEveningDelivery(): bool
    {
        return $this->allowEveningDelivery;
    }

    /**
     * @param bool $allowEveningDelivery
     */
    public function setAllowEveningDelivery(bool $allowEveningDelivery): void
    {
        $this->allowEveningDelivery = $allowEveningDelivery;
    }

    /**
     * @return bool
     */
    public function isPriceEveningDeliveryEnabled(): bool
    {
        return $this->priceEveningDeliveryEnabled;
    }

    /**
     * @param bool $priceEveningDeliveryEnabled
     */
    public function setPriceEveningDeliveryEnabled(bool $priceEveningDeliveryEnabled): void
    {
        $this->priceEveningDeliveryEnabled = $priceEveningDeliveryEnabled;
    }

    /**
     * @return float
     */
    public function getPriceEveningDelivery(): float
    {
        return $this->priceEveningDelivery;
    }

    /**
     * @param float $priceEveningDelivery
     */
    public function setPriceEveningDelivery(float $priceEveningDelivery): void
    {
        $this->priceEveningDelivery = $priceEveningDelivery;
    }

    /**
     * @return bool
     */
    public function isPriceSignatureEnabled(): bool
    {
        return $this->priceSignatureEnabled;
    }

    /**
     * @param bool $priceSignatureEnabled
     */
    public function setPriceSignatureEnabled(bool $priceSignatureEnabled): void
    {
        $this->priceSignatureEnabled = $priceSignatureEnabled;
    }

    /**
     * @return float
     */
    public function getPriceSignature(): float
    {
        return $this->priceSignature;
    }

    /**
     * @param float $priceSignature
     */
    public function setPriceSignature(float $priceSignature): void
    {
        $this->priceSignature = $priceSignature;
    }

    /**
     * @return bool
     */
    public function isPriceOnlyRecipientEnabled(): bool
    {
        return $this->priceOnlyRecipientEnabled;
    }

    /**
     * @param bool $priceOnlyRecipientEnabled
     */
    public function setPriceOnlyRecipientEnabled(bool $priceOnlyRecipientEnabled): void
    {
        $this->priceOnlyRecipientEnabled = $priceOnlyRecipientEnabled;
    }

    /**
     * @return float
     */
    public function getPriceOnlyRecipient(): float
    {
        return $this->priceOnlyRecipient;
    }

    /**
     * @param float $priceOnlyRecipient
     */
    public function setPriceOnlyRecipient(float $priceOnlyRecipient): void
    {
        $this->priceOnlyRecipient = $priceOnlyRecipient;
    }

    /**
     * @return bool
     */
    public function isPricePickupEnabled(): bool
    {
        return $this->pricePickupEnabled;
    }

    /**
     * @param bool $pricePickupEnabled
     */
    public function setPricePickupEnabled(bool $pricePickupEnabled): void
    {
        $this->pricePickupEnabled = $pricePickupEnabled;
    }

    /**
     * @return float
     */
    public function getPricePickup(): float
    {
        return $this->pricePickup;
    }

    /**
     * @param float $pricePickup
     */
    public function setPricePickup(float $pricePickup): void
    {
        $this->pricePickup = $pricePickup;
    }

    /**
     * @return bool
     */
    public function isAllowSaturdayDelivery(): bool
    {
        return $this->allowSaturdayDelivery;
    }

    /**
     * @param bool $allowSaturdayDelivery
     */
    public function setAllowSaturdayDelivery(bool $allowSaturdayDelivery): void
    {
        $this->allowSaturdayDelivery = $allowSaturdayDelivery;
    }

    /**
     * @return bool
     */
    public function isAllowMondayDelivery(): bool
    {
        return $this->allowMondayDelivery;
    }

    /**
     * @param bool $allowMondayDelivery
     */
    public function setAllowMondayDelivery(bool $allowMondayDelivery): void
    {
        $this->allowMondayDelivery = $allowMondayDelivery;
    }

    /**
     * @return bool
     */
    public function isAllowPickupLocations(): bool
    {
        return $this->allowPickupLocations;
    }

    /**
     * @param bool $allowPickupLocations
     */
    public function setAllowPickupLocations(bool $allowPickupLocations): void
    {
        $this->allowPickupLocations = $allowPickupLocations;
    }

    /**
     * @return bool
     */
    public function isAllowSignature(): bool
    {
        return $this->allowSignature;
    }

    /**
     * @param bool $allowSignature
     */
    public function setAllowSignature(bool $allowSignature): void
    {
        $this->allowSignature = $allowSignature;
    }

    /**
     * @return bool
     */
    public function isAllowOnlyRecipient(): bool
    {
        return $this->allowOnlyRecipient;
    }

    /**
     * @param bool $allowOnlyRecipient
     */
    public function setAllowOnlyRecipient(bool $allowOnlyRecipient): void
    {
        $this->allowOnlyRecipient = $allowOnlyRecipient;
    }

    /**
     * @return string
     */
    public function getDropOffDays(): string
    {
        return $this->dropOffDays;
    }

    /**
     * @param string $dropOffDays
     */
    public function setDropOffDays(string $dropOffDays): void
    {
        $this->dropOffDays = $dropOffDays;
    }

    /**
     * @return bool
     */
    public function isCutoffTimeEnabled(): bool
    {
        return $this->cutoffTimeEnabled;
    }

    /**
     * @param bool $cutoffTimeEnabled
     */
    public function setCutoffTimeEnabled(bool $cutoffTimeEnabled): void
    {
        $this->cutoffTimeEnabled = $cutoffTimeEnabled;
    }

    /**
     * @return string
     */
    public function getCutoffTime(): string
    {
        return $this->cutoffTime;
    }

    /**
     * @param string $cutoffTime
     */
    public function setCutoffTime(string $cutoffTime): void
    {
        $this->cutoffTime = $cutoffTime;
    }

    /**
     * @return bool
     */
    public function isDeliveryDaysWindowEnabled(): bool
    {
        return $this->deliveryDaysWindowEnabled;
    }

    /**
     * @param bool $deliveryDaysWindowEnabled
     */
    public function setDeliveryDaysWindowEnabled(bool $deliveryDaysWindowEnabled): void
    {
        $this->deliveryDaysWindowEnabled = $deliveryDaysWindowEnabled;
    }

    /**
     * @return int
     */
    public function getDeliveryDaysWindow(): int
    {
        return $this->deliveryDaysWindow;
    }

    /**
     * @param int $deliveryDaysWindow
     */
    public function setDeliveryDaysWindow(int $deliveryDaysWindow): void
    {
        $this->deliveryDaysWindow = $deliveryDaysWindow;
    }

    /**
     * @return bool
     */
    public function isDropOffDelayEnabled(): bool
    {
        return $this->dropOffDelayEnabled;
    }

    /**
     * @param bool $dropOffDelayEnabled
     */
    public function setDropOffDelayEnabled(bool $dropOffDelayEnabled): void
    {
        $this->dropOffDelayEnabled = $dropOffDelayEnabled;
    }

    /**
     * @return int
     */
    public function getDropOffDelay(): int
    {
        return $this->dropOffDelay;
    }

    /**
     * @param int $dropOffDelay
     */
    public function setDropOffDelay(int $dropOffDelay): void
    {
        $this->dropOffDelay = $dropOffDelay;
    }

    /**
     * @return bool
     */
    public function isEnabledPostNL(): bool
    {
        return $this->enabledPostNL;
    }

    /**
     * @return bool
     */
    public function isAllowShowDeliveryDatePostNL(): bool
    {
        return $this->allowShowDeliveryDatePostNL;
    }

    /**
     * @param bool $allowShowDeliveryDatePostNL
     */
    public function setAllowShowDeliveryDatePostNL(bool $allowShowDeliveryDatePostNL): void
    {
        $this->allowShowDeliveryDatePostNL = $allowShowDeliveryDatePostNL;
    }

    /**
     * @return bool
     */
    public function isAllowMorningDeliveryPostNL(): bool
    {
        return $this->allowMorningDeliveryPostNL;
    }

    /**
     * @param bool $allowMorningDeliveryPostNL
     */
    public function setAllowMorningDeliveryPostNL(bool $allowMorningDeliveryPostNL): void
    {
        $this->allowMorningDeliveryPostNL = $allowMorningDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isPriceMorningDeliveryEnabledPostNL(): bool
    {
        return $this->priceMorningDeliveryEnabledPostNL;
    }

    /**
     * @param bool $priceMorningDeliveryEnabledPostNL
     */
    public function setPriceMorningDeliveryEnabledPostNL(bool $priceMorningDeliveryEnabledPostNL): void
    {
        $this->priceMorningDeliveryEnabledPostNL = $priceMorningDeliveryEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPriceMorningDeliveryPostNL(): float
    {
        return $this->priceMorningDeliveryPostNL;
    }

    /**
     * @param float $priceMorningDeliveryPostNL
     */
    public function setPriceMorningDeliveryPostNL(float $priceMorningDeliveryPostNL): void
    {
        $this->priceMorningDeliveryPostNL = $priceMorningDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isPriceStandardDeliveryEnabledPostNL(): bool
    {
        return $this->priceStandardDeliveryEnabledPostNL;
    }

    /**
     * @param bool $priceStandardDeliveryEnabledPostNL
     */
    public function setPriceStandardDeliveryEnabledPostNL(bool $priceStandardDeliveryEnabledPostNL): void
    {
        $this->priceStandardDeliveryEnabledPostNL = $priceStandardDeliveryEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPriceStandardDeliveryPostNL(): float
    {
        return $this->priceStandardDeliveryPostNL;
    }

    /**
     * @param float $priceStandardDeliveryPostNL
     */
    public function setPriceStandardDeliveryPostNL(float $priceStandardDeliveryPostNL): void
    {
        $this->priceStandardDeliveryPostNL = $priceStandardDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isPriceSameDayDeliveryEnabledPostNL(): bool
    {
        return $this->priceSameDayDeliveryEnabledPostNL;
    }

    /**
     * @param bool $priceSameDayDeliveryEnabledPostNL
     */
    public function setPriceSameDayDeliveryEnabledPostNL(bool $priceSameDayDeliveryEnabledPostNL): void
    {
        $this->priceSameDayDeliveryEnabledPostNL = $priceSameDayDeliveryEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPriceSameDayDeliveryPostNL(): float
    {
        return $this->priceSameDayDeliveryPostNL;
    }

    /**
     * @param float $priceSameDayDeliveryPostNL
     */
    public function setPriceSameDayDeliveryPostNL(float $priceSameDayDeliveryPostNL): void
    {
        $this->priceSameDayDeliveryPostNL = $priceSameDayDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isAllowEveningDeliveryPostNL(): bool
    {
        return $this->allowEveningDeliveryPostNL;
    }

    /**
     * @param bool $allowEveningDeliveryPostNL
     */
    public function setAllowEveningDeliveryPostNL(bool $allowEveningDeliveryPostNL): void
    {
        $this->allowEveningDeliveryPostNL = $allowEveningDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isPriceEveningDeliveryEnabledPostNL(): bool
    {
        return $this->priceEveningDeliveryEnabledPostNL;
    }

    /**
     * @param bool $priceEveningDeliveryEnabledPostNL
     */
    public function setPriceEveningDeliveryEnabledPostNL(bool $priceEveningDeliveryEnabledPostNL): void
    {
        $this->priceEveningDeliveryEnabledPostNL = $priceEveningDeliveryEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPriceEveningDeliveryPostNL(): float
    {
        return $this->priceEveningDeliveryPostNL;
    }

    /**
     * @param float $priceEveningDeliveryPostNL
     */
    public function setPriceEveningDeliveryPostNL(float $priceEveningDeliveryPostNL): void
    {
        $this->priceEveningDeliveryPostNL = $priceEveningDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isPriceSignatureEnabledPostNL(): bool
    {
        return $this->priceSignatureEnabledPostNL;
    }

    /**
     * @param bool $priceSignatureEnabledPostNL
     */
    public function setPriceSignatureEnabledPostNL(bool $priceSignatureEnabledPostNL): void
    {
        $this->priceSignatureEnabledPostNL = $priceSignatureEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPriceSignaturePostNL(): float
    {
        return $this->priceSignaturePostNL;
    }

    /**
     * @param float $priceSignaturePostNL
     */
    public function setPriceSignaturePostNL(float $priceSignaturePostNL): void
    {
        $this->priceSignaturePostNL = $priceSignaturePostNL;
    }

    /**
     * @return bool
     */
    public function isPriceOnlyRecipientEnabledPostNL(): bool
    {
        return $this->priceOnlyRecipientEnabledPostNL;
    }

    /**
     * @param bool $priceOnlyRecipientEnabledPostNL
     */
    public function setPriceOnlyRecipientEnabledPostNL(bool $priceOnlyRecipientEnabledPostNL): void
    {
        $this->priceOnlyRecipientEnabledPostNL = $priceOnlyRecipientEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPriceOnlyRecipientPostNL(): float
    {
        return $this->priceOnlyRecipientPostNL;
    }

    /**
     * @param float $priceOnlyRecipientPostNL
     */
    public function setPriceOnlyRecipientPostNL(float $priceOnlyRecipientPostNL): void
    {
        $this->priceOnlyRecipientPostNL = $priceOnlyRecipientPostNL;
    }

    /**
     * @return bool
     */
    public function isPricePickupEnabledPostNL(): bool
    {
        return $this->pricePickupEnabledPostNL;
    }

    /**
     * @param bool $pricePickupEnabledPostNL
     */
    public function setPricePickupEnabledPostNL(bool $pricePickupEnabledPostNL): void
    {
        $this->pricePickupEnabledPostNL = $pricePickupEnabledPostNL;
    }

    /**
     * @return float
     */
    public function getPricePickupPostNL(): float
    {
        return $this->pricePickupPostNL;
    }

    /**
     * @param float $pricePickupPostNL
     */
    public function setPricePickupPostNL(float $pricePickupPostNL): void
    {
        $this->pricePickupPostNL = $pricePickupPostNL;
    }

    /**
     * @return bool
     */
    public function isAllowSaturdayDeliveryPostNL(): bool
    {
        return $this->allowSaturdayDeliveryPostNL;
    }

    /**
     * @param bool $allowSaturdayDeliveryPostNL
     */
    public function setAllowSaturdayDeliveryPostNL(bool $allowSaturdayDeliveryPostNL): void
    {
        $this->allowSaturdayDeliveryPostNL = $allowSaturdayDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isAllowMondayDeliveryPostNL(): bool
    {
        return $this->allowMondayDeliveryPostNL;
    }

    /**
     * @param bool $allowMondayDeliveryPostNL
     */
    public function setAllowMondayDeliveryPostNL(bool $allowMondayDeliveryPostNL): void
    {
        $this->allowMondayDeliveryPostNL = $allowMondayDeliveryPostNL;
    }

    /**
     * @return bool
     */
    public function isAllowPickupLocationsPostNL(): bool
    {
        return $this->allowPickupLocationsPostNL;
    }

    /**
     * @param bool $allowPickupLocationsPostNL
     */
    public function setAllowPickupLocationsPostNL(bool $allowPickupLocationsPostNL): void
    {
        $this->allowPickupLocationsPostNL = $allowPickupLocationsPostNL;
    }

    /**
     * @return bool
     */
    public function isAllowSignaturePostNL(): bool
    {
        return $this->allowSignaturePostNL;
    }

    /**
     * @param bool $allowSignaturePostNL
     */
    public function setAllowSignaturePostNL(bool $allowSignaturePostNL): void
    {
        $this->allowSignaturePostNL = $allowSignaturePostNL;
    }

    /**
     * @return bool
     */
    public function isAllowOnlyRecipientPostNL(): bool
    {
        return $this->allowOnlyRecipientPostNL;
    }

    /**
     * @param bool $allowOnlyRecipientPostNL
     */
    public function setAllowOnlyRecipientPostNL(bool $allowOnlyRecipientPostNL): void
    {
        $this->allowOnlyRecipientPostNL = $allowOnlyRecipientPostNL;
    }

    /**
     * @return string
     */
    public function getDropOffDaysPostNL(): string
    {
        return $this->dropOffDaysPostNL;
    }

    /**
     * @param string $dropOffDaysPostNL
     */
    public function setDropOffDaysPostNL(string $dropOffDaysPostNL): void
    {
        $this->dropOffDaysPostNL = $dropOffDaysPostNL;
    }

    /**
     * @return bool
     */
    public function isCutoffTimeEnabledPostNL(): bool
    {
        return $this->cutoffTimeEnabledPostNL;
    }

    /**
     * @param bool $cutoffTimeEnabledPostNL
     */
    public function setCutoffTimeEnabledPostNL(bool $cutoffTimeEnabledPostNL): void
    {
        $this->cutoffTimeEnabledPostNL = $cutoffTimeEnabledPostNL;
    }

    /**
     * @return string
     */
    public function getCutoffTimePostNL(): string
    {
        return $this->cutoffTimePostNL;
    }

    /**
     * @param string $cutoffTimePostNL
     */
    public function setCutoffTimePostNL(string $cutoffTimePostNL): void
    {
        $this->cutoffTimePostNL = $cutoffTimePostNL;
    }

    /**
     * @return bool
     */
    public function isDeliveryDaysWindowEnabledPostNL(): bool
    {
        return $this->deliveryDaysWindowEnabledPostNL;
    }

    /**
     * @param bool $deliveryDaysWindowEnabledPostNL
     */
    public function setDeliveryDaysWindowEnabledPostNL(bool $deliveryDaysWindowEnabledPostNL): void
    {
        $this->deliveryDaysWindowEnabledPostNL = $deliveryDaysWindowEnabledPostNL;
    }

    /**
     * @return int
     */
    public function getDeliveryDaysWindowPostNL(): int
    {
        return $this->deliveryDaysWindowPostNL;
    }

    /**
     * @param int $deliveryDaysWindowPostNL
     */
    public function setDeliveryDaysWindowPostNL(int $deliveryDaysWindowPostNL): void
    {
        $this->deliveryDaysWindowPostNL = $deliveryDaysWindowPostNL;
    }

    /**
     * @return bool
     */
    public function isDropOffDelayEnabledPostNL(): bool
    {
        return $this->dropOffDelayEnabledPostNL;
    }

    /**
     * @param bool $dropOffDelayEnabledPostNL
     */
    public function setDropOffDelayEnabledPostNL(bool $dropOffDelayEnabledPostNL): void
    {
        $this->dropOffDelayEnabledPostNL = $dropOffDelayEnabledPostNL;
    }

    /**
     * @return int
     */
    public function getDropOffDelayPostNL(): int
    {
        return $this->dropOffDelayPostNL;
    }

    /**
     * @param int $dropOffDelayPostNL
     */
    public function setDropOffDelayPostNL(int $dropOffDelayPostNL): void
    {
        $this->dropOffDelayPostNL = $dropOffDelayPostNL;
    }



}
