<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ClientTokenTest extends Setup
{
    public function testDefaultVersion_isTwo()
    {
        $this->assertEquals(2, Braintree\ClientToken::DEFAULT_VERSION);
    }

    public function testGenerateSignature_containsExpectedKeys()
    {
        $signature = Braintree\ClientToken::generateSignature();

        $this->assertContains('customerId', $signature);
        $this->assertContains('merchantAccountId', $signature);
        $this->assertContains('version', $signature);
    }

    public function testGenerateSignature_containsOptions()
    {
        $signature = Braintree\ClientToken::generateSignature();

        $optionsSignature = null;
        foreach ($signature as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('options', $value)) {
                $optionsSignature = $value['options'];
            }
        }

        $this->assertNotNull($optionsSignature);
        $this->assertContains('makeDefault', $optionsSignature);
        $this->assertContains('verifyCard', $optionsSignature);
        $this->assertContains('failOnDuplicatePaymentMethod', $optionsSignature);
    }

    public function testGenerateSignature_containsDomains()
    {
        $signature = Braintree\ClientToken::generateSignature();

        $domainsSignature = null;
        foreach ($signature as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('domains', $value)) {
                $domainsSignature = $value['domains'];
            }
        }

        $this->assertNotNull($domainsSignature);
    }
}
