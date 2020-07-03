<?php declare(strict_types=1);

namespace Kiener\KienerMyParcel\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1593593459ShippingOptions extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1593593459;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `kiener_my_parcel_shipping_option` (
              `id` BINARY(16) NOT NULL,
              `carrier_id` INTEGER NOT NULL,
              `package_type` INTEGER NOT NULL,
              `requires_age_check` TINYINT NOT NULL,
              `requires_signature` VARCHAR(255) NOT NULL,
              `only_recipient` VARCHAR(255) NOT NULL,
              `return_if_not_home` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
