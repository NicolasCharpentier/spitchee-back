<?php

namespace Spitchee\Util\HttpClient;

use Curl\Curl;

class CurlClient extends Curl
{
    public function getResponse() {
        return $this->response;
    }

    public function getBrutResponse() {
        return $this->rawResponse;
    }

    protected function buildURI($url, $params) {
        foreach ($params as $key => $value) {
            $url = str_replace(":$key", urlencode($value), $url);
        }
        
        return $url;
    }

    protected function fluidPost($url, $data = [], $follow303 = false) {
        $this->post($url, $data, $follow303);

        return $this;
    }

    protected function fluidJsonPost($url, $data = [], $follow303 = false) {
        $this->setHeader('Content-type', 'application/json');

        return $this->fluidPost($url, $data, $follow303);
    }
}