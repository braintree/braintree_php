<?php
namespace Braintree\HttpHelpers;

class Curl {

    public static function makeRequest($httpVerb, $url, $config, $httpRequest, $requestBody = null, $file = null, $customHeaders = null)
    {
        $httpRequest->setOption(CURLOPT_TIMEOUT, $config->getTimeout());
        $httpRequest->setOption(CURLOPT_CUSTOMREQUEST, $httpVerb);
        $httpRequest->setOption(CURLOPT_URL, $url);

        if ($config->getAcceptGzipEncoding()) {
            $httpRequest->setOption(CURLOPT_ENCODING, 'gzip');
        }
        if ($config->getSslVersion()) {
            $httpRequest->setOption(CURLOPT_SSLVERSION, $config->getSslVersion());
        }
    }
}
