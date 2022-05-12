<?php declare(strict_types=1);

namespace MyPa\Shopware\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1652167040DeactivateOldShipping extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1652167040;
    }

    public function update(Connection $connection): void
    {
        $fromBytesToHex = function (string $input): string {
            return Uuid::fromBytesToHex($input);
        };

        try {
            $result = $connection->fetchFirstColumn("SELECT shipping_method_id FROM kiener_my_parcel_shipping_method");
            if (count($result) < 0) {
                return;
            }
            $myParcelShippingMethodIds = array_map($fromBytesToHex, $result);

            foreach ($myParcelShippingMethodIds as $myParcelShippingMethodId) {
                //Change the description
                $connection->update('shipping_method_translation',
                    array('description' => 'This shipping method has been deprecated. Do not activate.'),
                    array('shipping_method_id' => Uuid::fromHexToBytes($myParcelShippingMethodId)));
                //Set the shipping on inactive
                $connection->update('shipping_method',
                    array('active' => 0),
                    array('id' => Uuid::fromHexToBytes($myParcelShippingMethodId)));
            }

        } catch (Exception\TableNotFoundException $e) {
            //Table does not exist, lets exit
            return;
        }
    }


    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
