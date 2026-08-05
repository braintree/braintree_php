<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class FacilitatedDetailsTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $details = Braintree\FacilitatedDetails::factory([]);
        $this->assertInstanceOf('Braintree\FacilitatedDetails', $details);
    }

    public function testFactory_setsAttributes()
    {
        $details = Braintree\FacilitatedDetails::factory([
            'merchantId' => 'merchant_abc',
            'merchantName' => 'Acme Store',
            'paymentMethodNonce' => 'fake-nonce-123',
        ]);

        $this->assertEquals('merchant_abc', $details->merchantId);
        $this->assertEquals('Acme Store', $details->merchantName);
        $this->assertEquals('fake-nonce-123', $details->paymentMethodNonce);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $details = Braintree\FacilitatedDetails::factory(['merchantName' => 'Acme Store']);
        $this->assertStringContainsString('FacilitatedDetails', (string) $details);
    }
}
