<?php namespace Braintree\Tests\Integration;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\Tests\TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class ClientTokenTest extends \PHPUnit_Framework_TestCase
{
    function test_ClientTokenAuthorizesRequest()
    {
        $clientToken = TestHelper::decodedClientToken();
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;
        $http = new HttpClientApi(Configuration::$global);
        $response = $http->get_cards(array(
            "authorization_fingerprint"       => $authorizationFingerprint,
            "shared_customer_identifier"      => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        ));

        $this->assertEquals(200, $response["status"]);
    }

    function test_VersionOptionSupported()
    {
        $clientToken = ClientToken::generate(array("version" => 1));
        $version = json_decode($clientToken)->version;
        $this->assertEquals(1, $version);
    }

    function test_VersionDefaultsToTwo()
    {
        $encodedClientToken = ClientToken::generate();
        $clientToken = base64_decode($encodedClientToken);
        $version = json_decode($clientToken)->version;
        $this->assertEquals(2, $version);
    }

    function testGateway_VersionDefaultsToTwo()
    {
        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $encodedClientToken = $gateway->clientToken()->generate();
        $clientToken = base64_decode($encodedClientToken);
        $version = json_decode($clientToken)->version;
        $this->assertEquals(2, $version);
    }

    function test_GatewayRespectsVerifyCard()
    {
        $result = Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $clientToken = TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
            "options"    => array(
                "verifyCard" => true
            )
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new HttpClientApi(Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card"                     => array(
                "number"         => "4000111111111115",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint"       => $authorizationFingerprint,
            "shared_customer_identifier"      => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));

        $this->assertEquals(422, $response["status"]);
    }

    function test_GatewayRespectsFailOnDuplicatePaymentMethod()
    {
        $result = Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $clientToken = TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new HttpClientApi(Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card"                     => array(
                "number"         => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint"       => $authorizationFingerprint,
            "shared_customer_identifier"      => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));
        $this->assertEquals(201, $response["status"]);

        $clientToken = TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
            "options"    => array(
                "failOnDuplicatePaymentMethod" => true
            )
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new HttpClientApi(Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card"                     => array(
                "number"         => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint"       => $authorizationFingerprint,
            "shared_customer_identifier"      => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));
        $this->assertEquals(422, $response["status"]);
    }

    function test_GatewayRespectsMakeDefault()
    {
        $result = Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $result = CreditCard::create(array(
            'customerId'     => $customerId,
            'number'         => '4111111111111111',
            'expirationDate' => '11/2099'
        ));
        $this->assertTrue($result->success);

        $clientToken = TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
            "options"    => array(
                "makeDefault" => true
            )
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new HttpClientApi(Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card"                     => array(
                "number"         => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint"       => $authorizationFingerprint,
            "shared_customer_identifier"      => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));

        $this->assertEquals(201, $response["status"]);

        $customer = Customer::find($customerId);
        $this->assertEquals(2, count($customer->creditCards));
        foreach ($customer->creditCards as $creditCard) {
            if ($creditCard->last4 == "4242") {
                $this->assertTrue($creditCard->default);
            }
        }
    }

    function test_ClientTokenAcceptsMerchantAccountId()
    {
        $clientToken = TestHelper::decodedClientToken(array(
            'merchantAccountId' => 'my_merchant_account'
        ));
        $merchantAccountId = json_decode($clientToken)->merchantAccountId;

        $this->assertEquals('my_merchant_account', $merchantAccountId);
    }

    function test_GenerateRaisesExceptionOnGateway422()
    {
        $this->setExpectedException('\Braintree\Exception\Unexpected', 'customer_id');

        ClientToken::generate(array(
            "customerId" => "not_a_customer"
        ));
    }
}
