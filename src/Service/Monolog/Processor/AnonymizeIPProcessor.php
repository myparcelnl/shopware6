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
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (!array_key_exists('ip', $record['extra'])) {
            return $record;
        }

        $record['extra']['ip'] = IpUtils::anonymize($record['extra']['ip']);

        return $record;
    }
}
