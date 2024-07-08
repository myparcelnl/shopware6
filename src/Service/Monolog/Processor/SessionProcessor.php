<?php

namespace MyPa\Shopware\Service\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    private $sessionId;

    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $this->sessionId = trim($request->getSession()->getId());
    }

    public function __invoke($record)
    {
        if (empty($this->sessionId)) {
            return $record;
        }

        $sessionPart = substr($this->sessionId, 0, 4) . '...';

        if (isset($record['message'])) {
            $record['message'] .= ' (Session: ' . $sessionPart . ')';
        }

        if (isset($record['formatted'])) {
            $record['formatted'] .= ' (Session: ' . $sessionPart . ')';
        }

        $record['extra'] = array_merge(
            $record['extra'],
            [
                'session' => $this->sessionId,
            ]
        );

        return $record;
    }
}
