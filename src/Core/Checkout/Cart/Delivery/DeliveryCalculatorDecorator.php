<?php declare(strict_types=1);

namespace MyPa\Shopware\Core\Checkout\Cart\Delivery;

use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use MyPa\Shopware\Service\ShippingOptions\ShippingOptionsService;
use Shopware\Core\Checkout\Cart\Cart;
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
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryCalculator;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class DeliveryCalculatorDecorator extends DeliveryCalculator
{
    public const CALCULATION_BY_LINE_ITEM_COUNT = 1;

    public const CALCULATION_BY_PRICE = 2;

    public const CALCULATION_BY_WEIGHT = 3;

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
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /**
     * @var ShippingOptionsService
     */
    private $shippingOptionsService;

    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(
        QuantityPriceCalculator $priceCalculator,
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder,
        TaxDetector $taxDetector,
        ShippingMethodService $shippingMethodService,
        ShippingOptionsService $shippingOptionsService,
        SystemConfigService $configService
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
        $this->taxDetector = $taxDetector;
        $this->shippingMethodService = $shippingMethodService;
        $this->shippingOptionsService = $shippingOptionsService;
        $this->configService = $configService;
    }

    public function calculate(CartDataCollection $data, Cart $cart, DeliveryCollection $deliveries, SalesChannelContext $context): void
    {
        foreach ($deliveries as $delivery) {
            $this->calculateDelivery($data, $cart, $delivery, $context);
        }
    }

    private function calculateDelivery(CartDataCollection $data, Cart $cart, Delivery $delivery, SalesChannelContext $context): void
    {
        $costs = null;

        if ($delivery->getShippingCosts()->getUnitPrice() > 0) {
            $costs = $this->calculateShippingCosts(
                new PriceCollection([
                    new Price(
                        Defaults::CURRENCY,
                        $delivery->getShippingCosts()->getTotalPrice(),
                        $delivery->getShippingCosts()->getTotalPrice(),
                        false
                    ),
                ]),
                $delivery->getPositions()->getLineItems(),
                $context
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        if ($this->hasDeliveryWithOnlyShippingFreeItems($delivery)) {
            $costs = $this->calculateShippingCosts(
                new PriceCollection([new Price(Defaults::CURRENCY, 0, 0, false)]),
                $delivery->getPositions()->getLineItems(),
                $context
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        $key = DeliveryProcessor::buildKey($delivery->getShippingMethod()->getId());

        if (!$data->has($key)) {
            throw new ShippingMethodNotFoundException($delivery->getShippingMethod()->getId());
        }

        /** @var ShippingMethodEntity $shippingMethod */
        $shippingMethod = $data->get($key);

        foreach ($context->getRuleIds() as $ruleId) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', $ruleId);

            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
            if ($costs !== null) {
                break;
            }
        }

        // Fetch default price if no rule matched
        if ($costs === null) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', null);
            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
        }

        if (!$costs) {
            $cart->addErrors(
                new ShippingMethodBlockedError((string) $shippingMethod->getTranslation('name'))
            );

            return;
        }

        $delivery->setShippingCosts($costs);
    }

    private function hasDeliveryWithOnlyShippingFreeItems(Delivery $delivery): bool
    {
        foreach ($delivery->getPositions()->getLineItems()->getIterator() as $lineItem) {
            if ($lineItem->getDeliveryInformation() && !$lineItem->getDeliveryInformation()->getFreeDelivery()) {
                return false;
            }
        }

        return true;
    }

    private function matches(Delivery $delivery, ShippingMethodPriceEntity $shippingMethodPrice, SalesChannelContext $context): bool
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

    private function calculateShippingCosts(PriceCollection $priceCollection, LineItemCollection $calculatedLineItems, SalesChannelContext $context): CalculatedPrice
    {
        $rules = $this->percentageTaxRuleBuilder->buildRules(
            $calculatedLineItems->getPrices()->sum()
        );

        $price = $this->getCurrencyPrice($priceCollection, $context);

        $shippingMethod = $this->shippingMethodService->getShippingMethodByShopwareShippingMethodId(
            $context->getShippingMethod()->getId(),
            new Context(new SystemSource())
        );

        if($shippingMethod) {
            $raise = $this->shippingOptionsService->getShippingOptionsRaisePrice();
            $price += $raise;
        }

        /* Backwards compatibility with 6.3*/
        if(method_exists($context->getContext(), 'getCurrencyPrecision')){
            $precision = $context->getContext()->getCurrencyPrecision();
            $definition = new QuantityPriceDefinition($price, $rules, $precision, 1, true);
        }else{
            $definition = new QuantityPriceDefinition($price, $rules, 1);
        }

        return $this->priceCalculator->calculate($definition, $context);
    }

    private function getCurrencyPrice(PriceCollection $priceCollection, SalesChannelContext $context): float
    {
        $price = $priceCollection->getCurrencyPrice($context->getCurrency()->getId());

        $value = $this->getPriceForTaxState($price, $context);

        if ($price->getCurrencyId() === Defaults::CURRENCY) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        $taxState = $this->taxDetector->getTaxState($context);

        if ($taxState === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }

    private function getMatchingPriceOfRule(Delivery $delivery, SalesChannelContext $context, ShippingMethodPriceCollection $shippingPrices): ?CalculatedPrice
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
                $price,
                $delivery->getPositions()->getLineItems(),
                $context
            );

            break;
        }

        return $costs;
    }
}
