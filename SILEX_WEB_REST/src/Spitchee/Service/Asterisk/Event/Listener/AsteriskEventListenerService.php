<?php

namespace Spitchee\Service\Asterisk\Event\Listener;


interface AsteriskEventListenerService
{
    public function processEvent($brutEventArray);
}