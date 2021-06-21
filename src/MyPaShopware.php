<?php declare(strict_types=1);

namespace MyPa\Shopware;

use MyPa\Shopware\Service\ShippingMethod\ShippingMethodService;
use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class MyPaShopware extends Plugin
{

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $this->updateCustomFields($installContext->getCurrentPluginVersion());
    }

    public function activate(ActivateContext $activateContext): void
    {
        /** @var ShippingMethodService $shippingMethodService */
        $shippingMethodService = $this->container->get(ShippingMethodService::class);

        // Install MyParcel shipping methods
        $shippingMethodService->createShippingMethods($activateContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);

        $connection->exec('SET FOREIGN_KEY_CHECKS=0;');
        $connection->exec('DROP TABLE IF EXISTS `kiener_my_parcel_shipment`');
        $connection->exec('DROP TABLE IF EXISTS `kiener_my_parcel_shipping_method`');
        $connection->exec('DROP TABLE IF EXISTS `kiener_my_parcel_shipping_option`');

        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1;');

        $this->deleteCustomFields($uninstallContext);
    }

    private function updateCustomFields(string $to, ?string $from = null)
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        if ($this->version_between('0.1.0', $to, $from)) {
            $this->updateCustomFields_0_1_0($customFieldSetRepository);
        }
    }

    private function version_between(string $between, string $to, ?string $from = null)
    {
        return version_compare($to, $between, '>=')
            && (is_null($from) || version_compare($from, $between, '<'));
    }

    private function deleteCustomFields()
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $entityIds = $customFieldSetRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'myparcelShopware')),
            Context::createDefaultContext()
        )->getEntities()->getIds();

        if (count($entityIds) < 1) {
            return;
        }

        $entityIds = array_map(function ($element) {
            return ['id' => $element];
        }, array_values($entityIds));

        $customFieldSetRepository->delete(
            $entityIds,
            Context::createDefaultContext()
        );
    }

    private function updateCustomFields_0_1_0(EntityRepositoryInterface $customFieldSetRepository)
    {
        $customFieldSetRepository->upsert([
            [
                'name' => 'myparcelShopware',
                'global' => true,
                'customFields' => [
                    [
                        'name' => 'my_parcel',
                        'type' => CustomFieldTypes::JSON,
                    ]
                ],
                'relations' => [
                    [
                        'entityName' => $this->container->get(OrderDefinition::class)->getEntityName()
                    ]
                ],
            ]
        ], Context::createDefaultContext());
    }
}
