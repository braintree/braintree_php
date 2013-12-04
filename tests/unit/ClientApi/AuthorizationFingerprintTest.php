<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class AuthorizationFingerprintTest extends PHPUnit_Framework_TestCase
{

    function testGenerate_containsRequiredData()
    {
        $fingerprint = Braintree_AuthorizationFingerprint::generate(array());
        $this->assertContains("merchant_id=integration_merchant_id", $fingerprint);
        $this->assertContains("public_key=integration_public_key", $fingerprint);
        $this->assertContains("created_at=", $fingerprint);
    }

    function testGenerate_optionallyTakesCustomerId()
    {
        $fingerprint = Braintree_AuthorizationFingerprint::generate(array("customerId" => 1));
        $this->assertContains("customer_id=1", $fingerprint);
    }

    function testErrorsWhenCreditCardOptionsGivenWithoutCustomerId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_AuthorizationFingerprint::generate(array("makeDefault" => true));
    }

}
