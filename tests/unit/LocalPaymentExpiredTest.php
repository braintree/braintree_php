<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class LocalPaymentExpiredTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $expired = Braintree\LocalPaymentExpired::factory([]);
        $this->assertInstanceOf('Braintree\LocalPaymentExpired', $expired);
    }

    public function testFactory_setsAttributes()
    {
        $expired = Braintree\LocalPaymentExpired::factory([
            'paymentId' => 'pay_123',
            'paymentContextId' => 'ctx_456',
        ]);

        $this->assertEquals('pay_123', $expired->paymentId);
        $this->assertEquals('ctx_456', $expired->paymentContextId);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $expired = Braintree\LocalPaymentExpired::factory(['paymentId' => 'pay_123']);
        $this->assertStringContainsString('LocalPaymentExpired', (string) $expired);
    }
}
