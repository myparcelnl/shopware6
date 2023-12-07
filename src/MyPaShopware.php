<?php declare(strict_types=1);

namespace MyPa\Shopware;

use Doctrine\DBAL\Connection;
use MyPa\Shopware\Service\Shopware\CustomField\CustomFieldInstaller;
use MyPa\Shopware\Service\Shopware\ShippingMethod\ShippingMethodCreatorService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class MyPaShopware extends Plugin
{

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        CustomFieldInstaller::createFactory($this->container)->install($installContext->getContext());
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        $shippingMethodCreator = $this->container->get(ShippingMethodCreatorService::class);
        $shippingMethodCreator->create($this->getPath(), $activateContext->getContext());
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $shippingMethodCreator = $this->container->get(ShippingMethodCreatorService::class);
        $shippingMethodCreator->create($this->getPath(), $updateContext->getContext());
        CustomFieldInstaller::createFactory($this->container)->install($updateContext->getContext());
    }


    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);

        $off = 0;
        $connection->exec("SET FOREIGN_KEY_CHECKS=$off;");
        $connection->exec('DROP TABLE IF EXISTS `myparcel_shipment`');
        $connection->exec('DROP TABLE IF EXISTS `myparcel_shipping_option`');

        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1;');

        $this->deleteCustomFields($uninstallContext);
    }

    private function deleteCustomFields(UninstallContext $uninstallContext)
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $entityIds = $customFieldSetRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'myparcelShopware')),
            $uninstallContext->getContext()
        )->getEntities()->getIds();

        if (count($entityIds) < 1) {
            return;
        }

        $entityIds = array_map(function ($element) {
            return ['id' => $element];
        }, array_values($entityIds));

        $customFieldSetRepository->delete(
            $entityIds,
            $uninstallContext->getContext()
        );
    }
}
