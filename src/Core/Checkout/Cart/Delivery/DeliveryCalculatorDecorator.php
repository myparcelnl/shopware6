<?php declare(strict_types=1);

namespace MyPa\Shopware\Core\Checkout\Cart\Delivery;

use MyPa\Shopware\Defaults as MyParcelDefaults;
use MyPa\Shopware\Service\Config\ConfigGenerator;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryCalculator;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor;
use Shopware\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use Shopware\Core\Checkout\Cart\Tax\TaxDetector;
use Shopware\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use Shopware\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceEntity;
use Shopware\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Shopware\Core\Checkout\Shipping\Exception\ShippingMethodNotFoundException;
use Shopware\Core\Checkout\Shipping\ShippingException;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use stdClass;

class DeliveryCalculatorDecorator extends DeliveryCalculator
{
    /**
     * @var QuantityPriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    /**
     * @var TaxDetector
     */
    private $taxDetector;

    /**
     * @var EntityRepository
     */
    private $shippingMethodRepository;

    /**
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * @param QuantityPriceCalculator   $priceCalculator
     * @param PercentageTaxRuleBuilder  $percentageTaxRuleBuilder
     * @param TaxDetector               $taxDetector
     * @param EntityRepository $shippingMethodRepository
     * @param ConfigGenerator           $configGenerator
     */
    public function __construct(
        QuantityPriceCalculator   $priceCalculator,
        PercentageTaxRuleBuilder  $percentageTaxRuleBuilder,
        TaxDetector               $taxDetector,
        EntityRepository $shippingMethodRepository,
        ConfigGenerator           $configGenerator
    )
    {
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
        $this->taxDetector = $taxDetector;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->configGenerator = $configGenerator;

        parent::__construct($priceCalculator, $percentageTaxRuleBuilder);
    }

    /**
     * @param CartDataCollection  $data
     * @param Cart                $cart
     * @param DeliveryCollection  $deliveries
     * @param SalesChannelContext $context
     * @return void
     */
    public function calculate(
        CartDataCollection  $data,
        Cart                $cart,
        DeliveryCollection  $deliveries,
        SalesChannelContext $context
    ): void
    {
        foreach ($deliveries as $delivery) {
            $this->calculateDelivery($data, $cart, $delivery, $context);
        }
    }

    /**
     * @param CartDataCollection  $data
     * @param Cart                $cart
     * @param Delivery            $delivery
     * @param SalesChannelContext $context
     * @return void
     */
    private function calculateDelivery(
        CartDataCollection  $data,
        Cart                $cart,
        Delivery            $delivery,
        SalesChannelContext $context
    ): void
    {
        $costs = null;
        $shippingMethod = $delivery->getShippingMethod();

        if ($delivery->getShippingCosts()->getUnitPrice() > 0) {
            $costs = $this->calculateShippingCosts(
                $shippingMethod,
                new PriceCollection([
                    new Price(
                        Defaults::CURRENCY,
                        $delivery->getShippingCosts()->getTotalPrice(),
                        $delivery->getShippingCosts()->getTotalPrice(),
                        false
                    ),
                ]),
                $delivery->getPositions()->getLineItems(),
                $context,
                $cart
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        if ($this->hasDeliveryWithOnlyShippingFreeItems($delivery)) {
            $costs = $this->calculateShippingCosts(
                $shippingMethod,
                new PriceCollection([new Price(Defaults::CURRENCY, 0, 0, false)]),
                $delivery->getPositions()->getLineItems(),
                $context,
                $cart
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        $key = DeliveryProcessor::buildKey($delivery->getShippingMethod()->getId());

        if (!$data->has($key)) {
            if (class_exists(ShippingMethodNotFoundException::class)) {
                throw new ShippingMethodNotFoundException($delivery->getShippingMethod()->getId());
            }
            /* Shopware 6.6: */
            throw ShippingException::shippingMethodNotFound($delivery->getShippingMethod()->getId());
        }

        /** @var ShippingMethodEntity $shippingMethod */
        $shippingMethod = $data->get($key);

        foreach ($context->getRuleIds() as $ruleId) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', $ruleId);

            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices, $cart);
            if ($costs !== null) {
                break;
            }
        }

        // Fetch default price if no rule matched
        if ($costs === null) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', null);
            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices, $cart);
        }

        if (!$costs) {
            $cart->addErrors(
                new ShippingMethodBlockedError((string)$shippingMethod->getTranslation('name'))
            );

            return;
        }

        $delivery->setShippingCosts($costs);
    }

    /**
     * @param  \Shopware\Core\Checkout\Shipping\ShippingMethodEntity $shippingMethod
     * @param  PriceCollection                                       $priceCollection
     * @param  LineItemCollection                                    $calculatedLineItems
     * @param  SalesChannelContext                                   $context
     * @param  Cart                                                  $cart
     *
     * @return CalculatedPrice
     */
    private function calculateShippingCosts(
        ShippingMethodEntity $shippingMethod,
        PriceCollection      $priceCollection,
        LineItemCollection   $calculatedLineItems,
        SalesChannelContext  $context,
        Cart                 $cart
    ): CalculatedPrice {
        $rules = $this->percentageTaxRuleBuilder->buildRules(
            $calculatedLineItems->getPrices()->sum()
        );

        $price = $this->getCurrencyPrice($priceCollection, $context);

        //Check if it is a my parcel shipping method
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(
            new NotFilter(NotFilter::CONNECTION_OR, [
                new EqualsFilter('customFields.myparcel', null),
            ])
        );

        $cartExtension = $cart->getExtension(MyParcelDefaults::CART_EXTENSION_KEY);
        $myParcelData  = $cartExtension ? $cartExtension->getVars() : [];
        if (! empty($myParcelData)
            && ! empty($myParcelData['myparcel']['deliveryData'])
            && $context->getShippingMethod()->getId() === $shippingMethod->getId()
        ) {
            if (isset($myParcelData['myparcelPackageType'])) {
                $myParcelData['myparcel']['deliveryData']->packageType = $myParcelData['myparcelPackageType'];
            }
            /** @var stdClass $deliveryData */
            $deliveryData = $myParcelData['myparcel']['deliveryData'];
            $deliveryData = json_decode(json_encode($deliveryData), true);
            $price        = $this->configGenerator->getCostForCarrierWithOptions(
                $deliveryData,
                $context->getSalesChannelId(),
                $price
            );
        }
        $definition = new QuantityPriceDefinition($price, $rules, 1);

        return $this->priceCalculator->calculate($definition, $context);
    }

    /**
     * @param PriceCollection     $priceCollection
     * @param SalesChannelContext $context
     * @return float
     */
    private function getCurrencyPrice(PriceCollection $priceCollection, SalesChannelContext $context): float
    {
        $price = $priceCollection->getCurrencyPrice($context->getCurrency()->getId());

        $value = $this->getPriceForTaxState($price, $context);

        if ($price->getCurrencyId() === Defaults::CURRENCY) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    /**
     * @param Price               $price
     * @param SalesChannelContext $context
     * @return float
     */
    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        $taxState = $this->taxDetector->getTaxState($context);

        if ($taxState === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }

    /**
     * @param Delivery $delivery
     * @return bool
     */
    private function hasDeliveryWithOnlyShippingFreeItems(Delivery $delivery): bool
    {
        foreach ($delivery->getPositions()->getLineItems()->getIterator() as $lineItem) {
            if ($lineItem->getDeliveryInformation() && !$lineItem->getDeliveryInformation()->getFreeDelivery()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Delivery                      $delivery
     * @param SalesChannelContext           $context
     * @param ShippingMethodPriceCollection $shippingPrices
     * @param Cart                          $cart
     * @return CalculatedPrice|null
     */
    private function getMatchingPriceOfRule(
        Delivery                      $delivery,
        SalesChannelContext           $context,
        ShippingMethodPriceCollection $shippingPrices,
        Cart                          $cart
    ): ?CalculatedPrice
    {
        $shippingPrices->sort(
            function (ShippingMethodPriceEntity $priceEntityA, ShippingMethodPriceEntity $priceEntityB) use ($context) {
                $priceA = $this->getCurrencyPrice($priceEntityA->getCurrencyPrice(), $context);
                $priceB = $this->getCurrencyPrice($priceEntityB->getCurrencyPrice(), $context);
                return $priceA <=> $priceB;
            }
        );

        $costs = null;
        foreach ($shippingPrices as $shippingPrice) {
            if (!$this->matches($delivery, $shippingPrice, $context)) {
                continue;
            }

            $price = $shippingPrice->getCurrencyPrice();

            if (!$price) {
                continue;
            }

            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                $price,
                $delivery->getPositions()->getLineItems(),
                $context,
                $cart
            );

            break;
        }

        return $costs;
    }

    /**
     * @param Delivery                  $delivery
     * @param ShippingMethodPriceEntity $shippingMethodPrice
     * @param SalesChannelContext       $context
     * @return bool
     */
    private function matches(
        Delivery                  $delivery,
        ShippingMethodPriceEntity $shippingMethodPrice,
        SalesChannelContext       $context
    ): bool
    {
        if ($shippingMethodPrice->getCalculationRuleId()) {
            return in_array($shippingMethodPrice->getCalculationRuleId(), $context->getRuleIds(), true);
        }

        $start = $shippingMethodPrice->getQuantityStart();
        $end = $shippingMethodPrice->getQuantityEnd();

        switch ($shippingMethodPrice->getCalculation()) {
            case self::CALCULATION_BY_PRICE:
                $value = $delivery->getPositions()->getPrices()->sum()->getTotalPrice();

                break;
            case self::CALCULATION_BY_LINE_ITEM_COUNT:
                $value = $delivery->getPositions()->getQuantity();

                break;
            case self::CALCULATION_BY_WEIGHT:
                $value = $delivery->getPositions()->getWeight();

                break;
            default:
                $value = $delivery->getPositions()->getLineItems()->getPrices()->sum()->getTotalPrice() / 100;

                break;
        }

        // $end (optional) exclusive
        return ($value >= $start) && (!$end || $value <= $end);
    }
}
