<?php

namespace Test\unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ExchangeRateQuoteTest extends SetUp
{
    public function testFactory()
    {
        $exchangeRateQuote = Braintree\ExchangeRateQuote::factory([]);

        $this->assertInstanceOf('Braintree\ExchangeRateQuote', $exchangeRateQuote);
    }

    public function testIsset()
    {
        $quoteParams = [
                'id' => '1234',
                'baseAmount' =>
                 [
                     'value' => '10.00',
                     'currencyCode' => 'USD'
                 ],
                'quoteAmount' =>
                [
                     'value' => '730.58',
                     'currencyCode' => 'INR'
                ],
                'exchangeRate' => '',
                'expiresAt' => '2022-06-09T21:30:00.000000Z',
                'refreshesAt' => '2022-06-09T18:30:00.000000Z'

        ];
        $exchangeRateQuote = Braintree\ExchangeRateQuote::factory($quoteParams);
        $this->assertTrue(isset($exchangeRateQuote->baseAmount));
        $this->assertNotEmpty($exchangeRateQuote->baseAmount);
    }

    public function testIsNull()
    {
        $quoteParams = [
            'id' => '1234',
            'quoteAmount' =>
                [
                    'value' => '730.58',
                    'currencyCode' => 'INR'
                ],
            'exchangeRate' => '',
            'expiresAt' => '2022-06-09T21:30:00.000000Z',
            'refreshesAt' => '2022-06-09T18:30:00.000000Z'

        ];
        $exchangeRateQuote = Braintree\ExchangeRateQuote::factory($quoteParams);
        $this->assertTrue(is_null($exchangeRateQuote->baseAmount));
    }
}
