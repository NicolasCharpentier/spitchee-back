<?php

namespace Spitchee\Service\Asterisk\Event;

use Container;
use Spitchee\Service\Asterisk\Event\Listener\AsteriskEventListenerService;

class AsteriskEventListenerLocator
{
    /**
     * @param Container $app
     * @param $type
     * @return AsteriskEventListenerService
     */
    public static function get(Container $app, $type)
    {
        if (AsteriskEventsDefinitionService::isNotWantedType($type))
        {
            return $app->getSpitcheeService('AsteriskNullEventListener');
        }
        else if (AsteriskEventsDefinitionService::isGenericHandlingType($type))
        {
            return $app->getSpitcheeService('AsteriskGenericEventListener');
        }
        else if (AsteriskEventsDefinitionService::TYPE_PEER_STATUS === $type) 
        {
            return $app->getSpitcheeService('AsteriskPeerStatusEventListener');
        }
        else if (AsteriskEventsDefinitionService::TYPE_ORIGINATE_RESPONSE === $type) 
        {
            return $app->getSpitcheeService('AsteriskOriginateResponseEventListener');
        }
        else if (AsteriskEventsDefinitionService::isConfBridgeType($type)) 
        {
            return $app->getSpitcheeService('AsteriskConfBridgeEventListener');
        }
        
        return $app->getSpitcheeService('AsteriskUnknownEventListener');
    }
}