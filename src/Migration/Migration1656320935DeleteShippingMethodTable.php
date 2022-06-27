<?php declare(strict_types=1);

namespace MyPa\Shopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1656320935DeleteShippingMethodTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1656320935;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
DROP TABLE IF EXISTS `kiener_my_parcel_shipping_method`
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
