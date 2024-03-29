<?php declare(strict_types=1);

namespace MyPa\Shopware\Service\Shopware\ShippingMethod;


use MyPa\Shopware\Defaults;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Rule\CartAmountRule;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\DeliveryTime\DeliveryTimeDefinition;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;

class ShippingMethodService
{
    private const NAMES = [
        'myparcel' => [
            'en-GB' => 'MyParcel',
            'nl-NL' => 'MyParcel',
            'nl-BE' => 'SendMyParcel',
            'de-DE' => 'MyParcel',
        ],
    ];

    /**
     * @var EntityRepository
     */
    private $deliveryTimeRepository;

    /**
     * @var EntityRepository
     */
    private $mediaRepository;

    /**
     * @var EntityRepository
     */
    private $ruleRepository;

    /**
     * @var EntityRepository
     */
    private $shippingMethodRepository;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(
        EntityRepository $deliveryTimeRepository,
        EntityRepository $mediaRepository,
        EntityRepository $ruleRepository,
        EntityRepository $shippingMethodRepository,
        MediaService              $mediaService,
        LoggerInterface           $logger
    )
    {
        $this->deliveryTimeRepository = $deliveryTimeRepository;
        $this->mediaRepository = $mediaRepository;
        $this->ruleRepository = $ruleRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->mediaService = $mediaService;
        $this->logger = $logger;
    }

    public function createShippingMethods(string $pluginDir, Context $context): void
    {
        try {
            $rule = $this->getCartAmountRule($context);
            $deliveryTime = $this->getDeliveryTime($context);
            $mediaId = $this->getMediaId($pluginDir, $context);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }


        $id = $this->createShippingMethod("myparcel", $rule->getId(), $deliveryTime->getId(), $mediaId, $context);

    }

    /**
     * Returns an availability rule. Creates it if it does not exist.
     *
     * @param Context $context
     * @return RuleEntity
     * @throws \Exception
     */
    private function getCartAmountRule(Context $context): RuleEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('moduleTypes', null));
        $criteria->addFilter(new EqualsFilter('conditions.type', (new CartAmountRule())->getName()));
        $criteria->addFilter(new EqualsFilter('conditions.value.amount', 0));
        $criteria->addFilter(new EqualsFilter('conditions.value.operator', CartAmountRule::OPERATOR_GTE));

        $rule = $this->ruleRepository->search($criteria, $context)->first();

        if ($rule instanceof RuleEntity) {
            return $rule;
        }

        $writeEvents = $this->ruleRepository->create(
            [
                [
                    'name' => 'Cart >= 0',
                    'priority' => 500,
                    'conditions' => [
                        [
                            'type' => (new CartAmountRule())->getName(),
                            'value' => [
                                'amount' => 0,
                                'operator' => CartAmountRule::OPERATOR_GTE
                            ]
                        ]
                    ]
                ]
            ],
            $context
        )->getEventByEntityName(RuleDefinition::ENTITY_NAME);

        if (count($writeEvents->getWriteResults()) > 0) {
            return $this->getCartAmountRule($context);
        }

        throw new \Exception('Could not get availability rule for shipping methods');
    }

    /**
     * Returns a delivery time. Creates it if it does not exist.
     *
     * @param Context $context
     * @return DeliveryTimeEntity|null
     * @throws \Exception
     */
    private function getDeliveryTime(Context $context): DeliveryTimeEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('min', 1));
        $criteria->addFilter(new EqualsFilter('max', 3));
        $criteria->addFilter(new EqualsFilter('unit', 'day'));

        $deliveryTime = $this->deliveryTimeRepository->search($criteria, $context)->first();

        if ($deliveryTime instanceof DeliveryTimeEntity) {
            return $deliveryTime;
        }

        $writeEvents = $this->deliveryTimeRepository->create(
            [
                [
                    'name' => '1-3 days',
                    'min' => 1,
                    'max' => 3,
                    'unit' => 'day',
                ]
            ],
            $context
        )->getEventByEntityName(DeliveryTimeDefinition::ENTITY_NAME);

        if (count($writeEvents->getWriteResults()) > 0) {
            return $this->getDeliveryTime($context);
        }

        throw new \Exception('Could not get delivery time for shipping methods');
    }

    private function getMediaId(string $pluginDir, Context $context): string
    {
        $fileName = 'myparcel-icon';

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fileName', $fileName));

        $icon = $this->mediaRepository->search($criteria, $context)->first();

        if ($icon instanceof MediaEntity) {
            return $icon->getId();
        }

        // Add icon to the media library
        $iconMime = 'image/png';
        $iconExt = 'png';
        $iconPath = realpath(implode(DIRECTORY_SEPARATOR, [
            $pluginDir,
            'Resources',
            'config',
            'plugin.png'
        ]));
        $iconBlob = file_get_contents($iconPath);

        return $this->mediaService->saveFile(
            $iconBlob,
            $iconExt,
            $iconMime,
            $fileName,
            $context,
            '',
            null,
            false
        );
    }

    public function createShippingMethod(
        string  $deliveryType,
        string  $ruleId,
        string  $deliveryTimeId,
        string  $mediaId,
        Context $context
    ): string
    {
        try {
            $existingShippingMethod = $this->getShippingMethodForType($deliveryType, $context);
            return $existingShippingMethod->getId();
        } catch (\Exception $e) {
            // Do nothing, it doesn't exist so create it.
        }

        $id = Uuid::randomHex();

        // Create the shipping method
        $event = $this->shippingMethodRepository->create([
            [
                'id' => $id,
                'name' => self::NAMES[$deliveryType],
                'active' => false,
                'availabilityRuleId' => $ruleId,
                'deliveryTimeId' => $deliveryTimeId,
                'mediaId' => $mediaId,
                'prices' => [
                    [
                        'calculation' => 1,
                        'currencyId' => $context->getCurrencyId(),
                        'price' => 0.0,
                        'quantityStart' => 1,
                    ]
                ],
                'customFields' => [
                    Defaults::CUSTOM_FIELDS_KEY => [
                        'deliveryType' => $deliveryType
                    ]
                ]
            ],
        ], $context);

        //?
        if (!empty($event->getErrors())) {
            $this->logger->error(
                implode(', ', $event->getErrors()),
                $event->getErrors()
            );
        }

        return $id;
    }

    /**
     * @param string $deliveryType
     * @param Context $context
     * @return ShippingMethodEntity
     * @throws \Exception
     */
    private function getShippingMethodForType(string $deliveryType, Context $context): ShippingMethodEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.' . Defaults::CUSTOM_FIELDS_KEY . '.deliveryType', $deliveryType));

        $shippingMethod = $this->shippingMethodRepository->search($criteria, $context)->first();

        if ($shippingMethod instanceof ShippingMethodEntity) {
            return $shippingMethod;
        }

        // TODO unique exception
        throw new \Exception('Method not found');
    }
}
