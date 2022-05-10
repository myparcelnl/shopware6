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
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class MyPaShopware extends Plugin
{

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);
        $this->deleteCustomFields();
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


}
