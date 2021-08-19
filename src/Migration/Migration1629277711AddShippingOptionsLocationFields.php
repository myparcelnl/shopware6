<?php declare(strict_types=1);

namespace MyPa\Shopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1629277711AddShippingOptionsLocationFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1629277711;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            ALTER TABLE `kiener_my_parcel_shipping_option`
            ADD `location_id` INT(11) NULL AFTER `large_format`,
            ADD `location_name` VARCHAR(255) NULL AFTER `location_id`,
            ADD `location_street` VARCHAR(255) NULL AFTER `location_name`,
            ADD `location_number` VARCHAR(255) NULL AFTER `location_street`,
            ADD `location_postalcode` VARCHAR(255) NULL AFTER `location_number`,
            ADD `location_city` VARCHAR(255) NULL AFTER `location_postalcode`,
            ADD `location_cc` VARCHAR(255) NULL AFTER `location_city`
            ADD `retail_network_id` VARCHAR(255) NULL AFTER `location_cc`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
