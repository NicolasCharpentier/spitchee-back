<?php

namespace Spitchee\Util\HttpClient;

class NamiClient extends CurlClient
{
    const URL_SIP_RELOAD = '/nami/action/sip/reload';
    const URL_ORIGINATE  = '/nami/action/originate/:confId/:peerId';
    const URL_CONF_KICK  = '/nami/action/conference/:confId/kick/:channelId';

    private $serviceUrl;

    /**
     * NamiClient constructor.
     * @param string $serviceUrl
     * @param int $defaultTimeOut
     * @throws \ErrorException
     */
    public function __construct($serviceUrl, $defaultTimeOut = 2)
    {
        parent::__construct();
        $this->setTimeout($defaultTimeOut);
        $this->serviceUrl = $serviceUrl;
        
        return $this;
    }

    public function sipReload() {
        return $this->fluidPost($this->serviceUrl . self::URL_SIP_RELOAD);
    }
    
    public function originate($confId, $sipId)
    {
        return $this->fluidPost($this->buildURI($this->serviceUrl . self::URL_ORIGINATE, [
            'confId' => $confId,
            'peerId' => $sipId
        ]));
    }

    public function kickFromConference($confId, $channelId)
    {
        return $this->fluidPost($this->buildURI($this->serviceUrl . self::URL_CONF_KICK, [
            'confId' => $confId,
            'channelId' => $channelId
        ]));
    }

    public function getResponse() {
        return $this->response;    
    }
}