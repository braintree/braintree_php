<?php

namespace Test\unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class MonetaryAmountTest extends Setup
{
    public function testFactory()
    {
        $monetaryAmount = Braintree\MonetaryAmount::factory([]);
        $this->assertInstanceOf('Braintree\MonetaryAmount', $monetaryAmount);
    }

    public function testMonetaryCurrencyCode()
    {
        $monetaryAmount = Braintree\MonetaryAmount::factory(['value' => '10.00', 'currencyCode' => 'USD']);
        $this->assertEquals('USD', $monetaryAmount->currencyCode);
    }

    public function testMonetaryValue()
    {
        $monetaryAmount = Braintree\MonetaryAmount::factory(['value' => '10.00', 'currencyCode' => 'USD']);
        $this->assertEquals('10.00', $monetaryAmount->value);
    }
}
