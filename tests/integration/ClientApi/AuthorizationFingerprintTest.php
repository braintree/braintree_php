<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

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

    public static function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/xml',
            'Content-Type: application/xml',
            'User-Agent: Braintree PHP Library ' . Braintree_Version::get(),
            'X-ApiVersion: ' . Braintree_Configuration::API_VERSION
        ));
        curl_setopt($curl, CURLOPT_USERPWD, Braintree_Configuration::publicKey() . ':' . Braintree_Configuration::privateKey());
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        if (Braintree_Configuration::sslOn()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, Braintree_Configuration::caFile());
        }

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (Braintree_Configuration::sslOn()) {
            if ($httpStatus == 0) {
                throw new Braintree_Exception_SSLCertificate();
            }
        }
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
}

class Braintree_AuthorizationFingerprintTest extends PHPUnit_Framework_TestCase
{
    function test_AuthorizationFingerprintAuthorizesRequest()
    {
        $fingerprint = Braintree_AuthorizationFingerprint::generate();
        $response = Braintree_HttpClientApi::get_cards(array(
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        ));

        $this->assertEquals(200, $response["status"]);
    }
}
