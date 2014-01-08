<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class AuthorizationInfoTest extends PHPUnit_Framework_TestCase
{

    function testGenerate_containsRequiredData()
    {
        $authInfo = json_decode(Braintree_AuthorizationInfo::generate(array()));
        $fingerprint = $authInfo->fingerprint;
        $this->assertContains("public_key=integration_public_key", $fingerprint);
        $this->assertContains("created_at=", $fingerprint);

        $clientApiUrl = "http://localhost:". Braintree_Configuration::portNumber() ."/merchants/integration_merchant_id/client_api";
        $this->assertEquals($clientApiUrl, $authInfo->client_api_url);

        $this->assertEquals("http://auth.venmo.dev:4567", $authInfo->auth_url);
    }

    function testGenerate_optionallyTakesCustomerId()
    {
        $fingerprint = json_decode(Braintree_AuthorizationInfo::generate(array("customerId" => 1)))->fingerprint;
        $this->assertContains("customer_id=1", $fingerprint);
    }

    function testErrorsWhenCreditCardOptionsGivenWithoutCustomerId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_AuthorizationInfo::generate(array("makeDefault" => true));
    }

}
