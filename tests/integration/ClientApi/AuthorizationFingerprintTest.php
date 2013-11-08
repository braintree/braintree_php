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

    function test_GatewayRespectsVerifyCard()
    {
        $result = Braintree_Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $fingerprint = Braintree_AuthorizationFingerprint::generate(array(
            "customer_id" => $customerId,
            "verifyCard" => true
        ));

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', http_build_query(array(
            "credit_card" => array(
                "number" => "4000111111111115",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        )));

        $this->assertEquals(422, $response["status"]);
    }

    function test_GatewayRespectsFailOnDuplicatePaymentMethod()
    {
        $result = Braintree_Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $fingerprint = Braintree_AuthorizationFingerprint::generate(array(
            "customer_id" => $customerId,
        ));

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', http_build_query(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        )));
        $this->assertEquals(200, $response["status"]);

        $fingerprint = Braintree_AuthorizationFingerprint::generate(array(
            "customer_id" => $customerId,
            "failOnDuplicatePaymentMethod" => true
        ));

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', http_build_query(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        )));
        $this->assertEquals(422, $response["status"]);
    }

    function test_GatewayRespectsMakeDefault()
    {
        $result = Braintree_Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $result = Braintree_CreditCard::create(array(
            'customerId' => $customerId,
            'number' => '4111111111111111',
            'expirationDate' => '11/2099'
        ));
        $this->assertTrue($result->success);

        $fingerprint = Braintree_AuthorizationFingerprint::generate(array(
            "customer_id" => $customerId,
            "makeDefault" => true
        ));

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', http_build_query(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        )));

        $this->assertEquals(200, $response["status"]);

        $customer = Braintree_Customer::find($customerId);
        $this->assertEquals(2, count($customer->creditCards));
        foreach ($customer->creditCards as $creditCard) {
            if ($creditCard->last4 == "4242") {
                $this->assertTrue($creditCard->default);
            }
        }
    }
}
