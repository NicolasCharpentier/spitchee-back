<?php

namespace Spitchee\Service\Asterisk\Event\Listener;

use Spitchee\Entity\NamiEvent;
use Spitchee\Service\Generic\ContainerAwareService;

class AsteriskGenericEventListenerService extends ContainerAwareService implements AsteriskEventListenerService
{
    public function processEvent($brutEventArray)
    {
        return new NamiEvent($brutEventArray['event'], $brutEventArray);   
    }
}