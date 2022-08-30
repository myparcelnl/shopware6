<?php

namespace MyPa\Shopware\Service\Shopware\CustomField;


use MyPa\Shopware\Service\Shopware\CustomField\Factory\CustomFieldFactory;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomFieldInstaller
{
    public static function createFactory(ContainerInterface $container)
    {
        return new self(
            CustomFieldFactory::createFactory($container),
        );
    }

    /**
     * @var CustomFieldFactory
     */
    protected $factory;

    public function __construct(CustomFieldFactory $customFieldFactory)
    {
        $this->factory = $customFieldFactory;
    }

    public function install(Context $context): void
    {
        $this->installHSFields($context);
    }

    private function installHSFields(Context $context): void
    {
        $setId = $this->factory->createSet(
            'myparcel_product',
            [
                'en-GB' => 'MyParcel',
                'de-DE' => 'MyParcel',
                'nl-NL' => 'MyParcel',
            ],
            [
                ProductDefinition::class,
            ],
            false,
            $context
        );

        $hsFieldId = $this->factory->createTextField(
            'myparcel_product_hs_code',
            [
                'en-GB' => 'HS Tariff Code',
                'de-DE' => 'HS-Zolltarifcode',
                'nl-NL' => 'GS-tariefcode',
            ],
            null,
            [
                'en-GB' => '000000',
            ],
            1,
            false,
            $context
        );

        $this->factory->addFieldsToSet(
            $setId,
            [
                $hsFieldId,
            ],
            $context
        );
    }
}
