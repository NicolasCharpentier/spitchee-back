<?php

namespace Spitchee\Util\HttpClient;


class SuClient extends CurlClient
{
    const URL_ADD_TO_CONFERENCE = '/conferences/add';
    const URL_TEST_CONNECTION   = '/';

    private $serviceUrl;

    /**
     * NamiClient constructor.
     * @param null $url
     * @param int $defaultTimeOut
     * @throws \ErrorException
     */
    public function __construct($url, $defaultTimeOut = 2)
    {
        parent::__construct();
        $this->setTimeout($defaultTimeOut);
        $this->serviceUrl = $url;
        return $this;
    }

    public function registerToConference($conferenceId, $sipId, $sipSecret)
    {
        return $this->fluidJsonPost($this->serviceUrl . self::URL_ADD_TO_CONFERENCE, [
            'conferenceId' => $conferenceId,
            'user' => [
                'id' => $sipId,
                'secret' => $sipSecret,
            ]
        ]);
    }

    public function testConnection()
    {
        return $this->fluidPost($this->serviceUrl . self::URL_TEST_CONNECTION);
    }
}