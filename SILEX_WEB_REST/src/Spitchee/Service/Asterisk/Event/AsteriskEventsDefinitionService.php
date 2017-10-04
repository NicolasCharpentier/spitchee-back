<?php

namespace Spitchee\Service\Asterisk\Event;

class AsteriskEventsDefinitionService
{
    // Prefixe des types non présents ici
    const TYPE_NOT_HANDLED = 'NotHandled';

    // Types enregistrés + custom deal
    const TYPE_PEER_STATUS          = 'PeerStatus';
    const TYPE_ORIGINATE_RESPONSE   = 'OriginateResponse';
    const TYPE_CONFBRIDGE_LEAVE     = 'ConfbridgeLeave';
    const TYPE_CONFBRIDGE_JOIN      = 'ConfbridgeJoin';
    const TYPE_CONFBRIDGE_END       = 'ConfbridgeEnd';
    const TYPE_CONFBRIDGE_START     = 'ConfbridgeStart';

    // Types enregistrés + generic deal
    const TYPE_HANGUP       = 'Hangup';
    const TYPE_FULLY_BOOTED = 'FullyBooted';

    // Types osef
    const TYPE_EXTENSION_STATUS = 'ExtensionStatus';
    const TYPE_HANGUP_REQUEST   = 'HangupRequest';
    const TYPE_VARSET           = 'VarSet';
    const TYPE_RTCPReceived     = 'RTCPReceived';
    const TYPE_RTCPSent         = 'RTCPSent';
    const TYPE_NEW_ACCOUNT_CODE = 'NewAccountCode';
    const TYPE_NEW_CALLER_ID    = 'NewCallerid';
    const TYPE_NEW_CHANNEL      = 'Newchannel';
    const TYPE_NEW_STATE        = 'Newstate';
    const TYPE_DTMF             = 'DTMF'; // Pressage des touches
    
    
    static public function isNotWantedType($type) {
        return in_array($type, [
            self::TYPE_EXTENSION_STATUS,
            self::TYPE_HANGUP_REQUEST,
            self::TYPE_VARSET,
            self::TYPE_RTCPReceived,
            self::TYPE_RTCPSent,
            self::TYPE_NEW_ACCOUNT_CODE,
            self::TYPE_NEW_CALLER_ID,
            self::TYPE_NEW_CHANNEL,
            self::TYPE_NEW_STATE,
            self::TYPE_DTMF,
        ]);
    }
    
    static public function isGenericHandlingType($type) {
        return in_array($type, [
            self::TYPE_HANGUP,
            self::TYPE_FULLY_BOOTED,
        ]);
    }
    
    static public function isConfBridgeType($type) {
        return in_array($type, [
            self::TYPE_CONFBRIDGE_START,
            self::TYPE_CONFBRIDGE_JOIN,
            self::TYPE_CONFBRIDGE_LEAVE,
            self::TYPE_CONFBRIDGE_END,
        ]);
    }
}