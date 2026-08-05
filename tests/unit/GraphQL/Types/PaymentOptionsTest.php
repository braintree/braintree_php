<?php

namespace Test\Unit\GraphQL\Types;

require_once dirname(__DIR__, 3) . '/Setup.php';

use Test\Setup;
use Braintree\GraphQL\Types\PaymentOptions;

class PaymentOptionsTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $options = PaymentOptions::factory([]);
        $this->assertInstanceOf(PaymentOptions::class, $options);
    }

    public function testFactory_setsPaymentOption()
    {
        $options = PaymentOptions::factory([
            'paymentOption' => 'PAYPAL',
            'recommendedPriority' => 1,
        ]);

        $this->assertEquals('PAYPAL', $options->paymentOption);
        $this->assertEquals(1, $options->recommendedPriority);
    }

    public function testFactory_paymentOptionNotSetWhenNotProvided()
    {
        $options = PaymentOptions::factory([]);
        $this->assertFalse(isset($options->paymentOption));
    }

    public function testFactory_recommendedPriorityNotSetWhenNotProvided()
    {
        $options = PaymentOptions::factory([]);
        $this->assertFalse(isset($options->recommendedPriority));
    }
}
