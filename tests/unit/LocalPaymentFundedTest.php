<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class LocalPaymentFundedTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $funded = Braintree\LocalPaymentFunded::factory([]);
        $this->assertInstanceOf('Braintree\LocalPaymentFunded', $funded);
    }

    public function testFactory_setsAttributes()
    {
        $funded = Braintree\LocalPaymentFunded::factory([
            'paymentId' => 'pay_123',
            'paymentContextId' => 'ctx_456',
        ]);

        $this->assertEquals('pay_123', $funded->paymentId);
        $this->assertEquals('ctx_456', $funded->paymentContextId);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $funded = Braintree\LocalPaymentFunded::factory([
            'paymentId' => 'pay_123',
        ]);

        $this->assertStringContainsString('LocalPaymentFunded', (string) $funded);
    }
}
