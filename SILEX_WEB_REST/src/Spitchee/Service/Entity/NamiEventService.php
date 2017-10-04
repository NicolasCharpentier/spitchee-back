<?php

namespace Spitchee\Service\Entity;

use Spitchee\Service\Asterisk\Event\AsteriskEventListenerLocator;
use Spitchee\Service\Generic\BaseEntityService;

class NamiEventService extends BaseEntityService
{
    public function handleNewEvent($eventArray) {
        if (false === $this->validateBrutEvent($eventArray)) {
            //return false;
            return null;
        }
        
        $event = AsteriskEventListenerLocator::get(
            $this->getContainer(), $eventArray['event']
        )->processEvent($eventArray);

        if (null === $event) {
            //return false;
            return null;
        }
    
        $this->persist($event);
        $this->flush();
        
        //return true;
        return $event;
    }

    private function validateBrutEvent($eventArray) {
        return isset($eventArray['event']);
    }
}