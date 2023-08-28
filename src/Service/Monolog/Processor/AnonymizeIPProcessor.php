<?php

namespace MyPa\Shopware\Service\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\IpUtils;

class AnonymizeIPProcessor implements ProcessorInterface
{
    /**
     * Gets an anonymous version of the
     * provided IP address string.
     *
     * @param  array|\Monolog\LogRecord $record 6.4 expects array, 6.5 expects logRecord, minimum php is 7.4 and 8.1 respectively
     *
     * @return array
     */
    public function __invoke($record)
    {
        if (!array_key_exists('ip', $record['extra'])) {
            return $record;
        }

        $record['extra']['ip'] = IpUtils::anonymize($record['extra']['ip']);

        return $record;
    }
}
