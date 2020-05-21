<?php
namespace Braintree\HttpHelpers;

class Curl {

    public static function makeRequest($httpVerb, $url, $config, $httpRequest, $requestBody = null, $file = null, $customHeaders = null)
    {
        $httpRequest->setOption(CURLOPT_TIMEOUT, $config->getTimeout());
    }
}
