<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_AuthorizationInfoTest extends PHPUnit_Framework_TestCase
{
    function test_AuthorizationInfoAuthorizesRequest()
    {
        $authInfo = Braintree_AuthorizationInfo::generate();
        $fingerprint = json_decode($authInfo)->fingerprint;
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

        $authInfo = Braintree_AuthorizationInfo::generate(array(
            "customerId" => $customerId,
            "verifyCard" => true
        ));
        $fingerprint = json_decode($authInfo)->fingerprint;

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', json_encode(array(
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

        $authInfo = Braintree_AuthorizationInfo::generate(array(
            "customerId" => $customerId,
        ));
        $fingerprint = json_decode($authInfo)->fingerprint;

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', json_encode(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        )));
        $this->assertEquals(201, $response["status"]);

        $authInfo = Braintree_AuthorizationInfo::generate(array(
            "customerId" => $customerId,
            "failOnDuplicatePaymentMethod" => true
        ));
        $fingerprint = json_decode($authInfo)->fingerprint;

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', json_encode(array(
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

        $authInfo = Braintree_AuthorizationInfo::generate(array(
            "customerId" => $customerId,
            "makeDefault" => true
        ));
        $fingerprint = json_decode($authInfo)->fingerprint;

        $response = Braintree_HttpClientApi::post('/client_api/credit_cards.json', json_encode(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $fingerprint,
            "session_identifier" => "fake_identifier",
            "session_identifier_type" => "testing"
        )));

        $this->assertEquals(201, $response["status"]);

        $customer = Braintree_Customer::find($customerId);
        $this->assertEquals(2, count($customer->creditCards));
        foreach ($customer->creditCards as $creditCard) {
            if ($creditCard->last4 == "4242") {
                $this->assertTrue($creditCard->default);
            }
        }
    }
}
