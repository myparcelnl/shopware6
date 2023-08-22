<?php declare(strict_types=1);

namespace MyPa\Shopware\Service\Monolog\Factory;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Shopware\Core\Framework\Log\Monolog\DoctrineSQLHandler;
use Shopware\Core\Kernel;

class LoggerFactory
{

    /**
     * @param $filename
     * @param $retentionDays
     * @return RotatingFileHandler
     */
    public function createFileHandler($filename, $retentionDays): RotatingFileHandler
    {
        return new RotatingFileHandler($filename, $retentionDays, Logger::INFO);
    }

    /**
     * @return DoctrineSQLHandler
     */
    public function createSQLHandler(): DoctrineSQLHandler
    {
        return new DoctrineSQLHandler(Kernel::getConnection(), Level::Info);
    }

}
