<?php

class Braintree_HttpClientApi extends Braintree_Http
{

    private static function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return self::_doUrlRequest($httpVerb, Braintree_Configuration::baseUrl() . $path, $requestBody);
    }

    public static function get($path)
    {
         return self::_doRequest('GET', $path);
    }

    public static function post($path, $body)
    {
         return self::_doRequest('POST', $path, $body);
    }

    public static function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-ApiVersion: ' . Braintree_Configuration::API_VERSION
        ));
        curl_setopt($curl, CURLOPT_USERPWD, Braintree_Configuration::publicKey() . ':' . Braintree_Configuration::privateKey());

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return array('status' => $httpStatus, 'body' => $response);
    }

    public static function get_cards($options) {
        $encoded_fingerprint = urlencode($options["authorization_fingerprint"]);
        $url = "/client_api/credit_cards.json?";
        $url .= "authorizationFingerprint=" . $encoded_fingerprint;
        $url .= "&sessionIdentifier=" . $options["session_identifier"];
        $url .= "&sessionIdentifierType=" . $options["session_identifier_type"];

        return Braintree_HttpClientApi::get($url);
    }

    public static function nonce_for_new_card($options) {
        $fingerprint = Braintree_AuthorizationFingerprint::generate();
        $options["authorization_fingerprint"] = $fingerprint;
        $options["session_identifier"] = "fake_identifier_" . rand();
        $options["session_identifier_type"] = "testing";
        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', json_encode($options));
        if ($response["status"] != 201) {
            throw new Exception(var_dump($response));
        } else {
            $body = json_decode($response["body"]);
            return $body->nonce;
        }
    }
}
