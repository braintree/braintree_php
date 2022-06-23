<?php

namespace Test\unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ExchangeRateQuoteInputTest extends Setup
{
    public function testFactory()
    {
        $exchangeRateQuoteInput = Braintree\ExchangeRateQuoteInput::factory([]);

        $this->assertInstanceOf('Braintree\ExchangeRateQuoteInput', $exchangeRateQuoteInput);
    }

    public function testToString()
    {
        $inputParams = [
            'baseCurrency' => 'USD',
            'quoteCurrency' => 'INR',
            'baseAmount' => '10.00',
            'markup' => '',
        ];

        $input = Braintree\ExchangeRateQuoteInput::factory($inputParams);
        $this->assertEquals("Braintree\ExchangeRateQuoteInput[baseCurrency=USD, quoteCurrency=INR, baseAmount=10.00, markup=]", (string) $input);
    }
}
