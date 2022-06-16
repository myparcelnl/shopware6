<?php declare(strict_types=1);

namespace MyPa\Shopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1652965408AddShipmentField extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1652965408;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE kiener_my_parcel_shipment
ADD shipment_status INT(11) default 1 NOT NULL AFTER `label_url`;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
