<?php

namespace Spitchee\Service\Asterisk\Event\Listener;

use Spitchee\Entity\NamiEvent;
use Spitchee\Service\Asterisk\Event\AsteriskEventsDefinitionService;
use Spitchee\Service\Generic\ContainerAwareService;

class AsteriskUnknownEventListenerService extends ContainerAwareService implements AsteriskEventListenerService
{
    public function processEvent($brutEventArray)
    {
        return new NamiEvent(
            AsteriskEventsDefinitionService::TYPE_NOT_HANDLED . ' - ' . $brutEventArray['event'],
            $brutEventArray
        );
    }
}