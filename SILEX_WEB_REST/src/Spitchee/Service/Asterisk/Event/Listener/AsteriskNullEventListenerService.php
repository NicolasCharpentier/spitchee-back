<?php

namespace Spitchee\Service\Asterisk\Event\Listener;

class AsteriskNullEventListenerService implements AsteriskEventListenerService
{
    public function processEvent($brutEventArray)
    {
        return null;
    }
}