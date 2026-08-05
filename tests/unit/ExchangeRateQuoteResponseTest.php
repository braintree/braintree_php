<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ExchangeRateQuoteResponseTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $response = Braintree\ExchangeRateQuoteResponse::factory([]);
        $this->assertInstanceOf('Braintree\ExchangeRateQuoteResponse', $response);
    }

    public function testFactory_quotesDefaultToEmptyArray()
    {
        $response = Braintree\ExchangeRateQuoteResponse::factory([]);
        $this->assertEquals([], $response->quotes);
    }

    public function testFactory_buildsQuoteObjects()
    {
        $response = Braintree\ExchangeRateQuoteResponse::factory([
            'quotes' => [
                [
                    'id' => 'quote_1',
                    'baseAmount' => ['value' => '10.00', 'currencyCode' => 'USD'],
                    'quoteAmount' => ['value' => '9.20', 'currencyCode' => 'EUR'],
                    'exchangeRate' => '0.92',
                    'expiresAt' => '2022-06-09T21:30:00.000000Z',
                    'refreshesAt' => '2022-06-09T18:30:00.000000Z',
                ],
                [
                    'id' => 'quote_2',
                    'baseAmount' => ['value' => '50.00', 'currencyCode' => 'USD'],
                    'quoteAmount' => ['value' => '46.00', 'currencyCode' => 'EUR'],
                    'exchangeRate' => '0.92',
                    'expiresAt' => '2022-06-09T21:30:00.000000Z',
                    'refreshesAt' => '2022-06-09T18:30:00.000000Z',
                ],
            ],
        ]);

        $this->assertCount(2, $response->quotes);
        $this->assertInstanceOf('Braintree\ExchangeRateQuote', $response->quotes[0]);
        $this->assertInstanceOf('Braintree\ExchangeRateQuote', $response->quotes[1]);
    }

    public function testFactory_setsQuoteFieldsCorrectly()
    {
        $response = Braintree\ExchangeRateQuoteResponse::factory([
            'quotes' => [
                [
                    'id' => 'quote_1',
                    'baseAmount' => ['value' => '10.00', 'currencyCode' => 'USD'],
                    'quoteAmount' => ['value' => '9.20', 'currencyCode' => 'EUR'],
                    'exchangeRate' => '0.92',
                    'expiresAt' => '2022-06-09T21:30:00.000000Z',
                    'refreshesAt' => '2022-06-09T18:30:00.000000Z',
                ],
            ],
        ]);

        $quote = $response->quotes[0];
        $this->assertEquals('0.92', $quote->exchangeRate);
        $this->assertEquals('USD', $quote->baseAmount->currencyCode);
        $this->assertEquals('10.00', $quote->baseAmount->value);
        $this->assertEquals('EUR', $quote->quoteAmount->currencyCode);
        $this->assertEquals('2022-06-09T21:30:00.000000Z', $quote->expiresAt);
    }
}
