<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class LocalPaymentReversedTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $reversed = Braintree\LocalPaymentReversed::factory([]);
        $this->assertInstanceOf('Braintree\LocalPaymentReversed', $reversed);
    }

    public function testFactory_setsAttributes()
    {
        $reversed = Braintree\LocalPaymentReversed::factory([
            'paymentId' => 'pay_123',
            'paymentContextId' => 'ctx_456',
        ]);
        $this->assertEquals('pay_123', $reversed->paymentId);
        $this->assertEquals('ctx_456', $reversed->paymentContextId);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $reversed = Braintree\LocalPaymentReversed::factory(['paymentId' => 'pay_123']);
        $this->assertStringContainsString('LocalPaymentReversed', (string) $reversed);
    }
}
