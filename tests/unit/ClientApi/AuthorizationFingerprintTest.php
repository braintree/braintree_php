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
        $fingerprint = Braintree_AuthorizationFingerprint::generate(array("customer_id" => 1));
        $this->assertContains("customer_id=1", $fingerprint);
    }

    function testGenerate_cannotOverwriteDefaults()
    {
        $fingerprint = Braintree_AuthorizationFingerprint::generate(array(
            "merchant_id" => "bad_id",
            "public_key" => "bad_key",
            "created_at" => "bad_time"
        ));
        $this->assertNotContains("merchant_id=bad_id", $fingerprint);
        $this->assertNotContains("public_key=bad_key", $fingerprint);
        $this->assertNotContains("created_at=bad_time", $fingerprint);
    }
}
