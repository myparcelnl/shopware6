<?php declare(strict_types=1);

namespace MyPa\Shopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1657543922RemoveKienerTableNaming extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1657543922;
    }

    public function update(Connection $connection): void
    {

        $query = <<<SQL
RENAME TABLE `kiener_my_parcel_shipment` TO `myparcel_shipment`;
RENAME TABLE `kiener_my_parcel_shipping_option` TO `myparcel_shipping_option`;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
