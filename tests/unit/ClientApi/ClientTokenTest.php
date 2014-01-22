<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class ClientTokenTest extends PHPUnit_Framework_TestCase
{

    function testGenerate_containsRequiredData()
    {
        $clientToken = json_decode(Braintree_ClientToken::generate(array()));
        $authorizationFingerprint = $clientToken->authorization_fingerprint;
        $this->assertContains("public_key=integration_public_key", $authorizationFingerprint);
        $this->assertContains("created_at=", $authorizationFingerprint);

        $clientApiUrl = "http://localhost:". Braintree_Configuration::portNumber() ."/merchants/integration_merchant_id/client_api";
        $this->assertEquals($clientApiUrl, $clientToken->client_api_url);

        $this->assertEquals("http://auth.venmo.dev:4567", $clientToken->auth_url);
    }

    function testGenerate_optionallyTakesCustomerId()
    {
        $authorizationFingerprint = json_decode(Braintree_ClientToken::generate(array("customerId" => 1)))->authorization_fingerprint;
        $this->assertContains("customer_id=1", $authorizationFingerprint);
    }

    function testErrorsWhenCreditCardOptionsGivenWithoutCustomerId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_ClientToken::generate(array("makeDefault" => true));
    }

}
