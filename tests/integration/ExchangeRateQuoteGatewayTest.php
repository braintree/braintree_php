<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Braintree;
use Braintree\ExchangeRateQuoteInput;
use Braintree\ExchangeRateQuoteRequest;
use Test\Setup;

class ExchangeRateQuoteGatewayTest extends Setup
{
    public function testGraphQLSandboxSSL()
    {
        Braintree\Configuration::environment('sandbox');
        $graphQL = new Braintree\GraphQL(Braintree\Configuration::$global);
        $definition = "query { ping }";

        $this->expectException('Braintree\Exception\Authentication');
        $response = $graphQL->request($definition, null);
    }

    public function testExchangeRateQuoteAPIwithvariableInputs()
    {
        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key',
        ]);

        $variables = [
            'quotes' => [
                [
                    'baseCurrency' => 'EUR',
                    'quoteCurrency' => 'CAD',
                    'baseAmount' => '15.16',
                    'markup' => '2.64'
                ],
                [
                    'baseCurrency' => 'USD',
                    'quoteCurrency' => 'EUR',
                    'baseAmount' => '12.19',
                    'markup' => '1.89'
                ],
            ]
        ];
        $request = ExchangeRateQuoteRequest::factory($variables);
        $response = $gateway->exchangeRateQuote()->generate($request);

        $quotes = $response->quotes;
        $monetaryBaseAmount = $quotes[0]->baseAmount;
        $monetaryQuoteAmount1 = $quotes[1]->quoteAmount;

        $this->assertArrayNotHasKey("errors", $response->quotes);
        $this->assertEquals("2", sizeof($quotes));
        $this->assertEquals("12.19", $monetaryBaseAmount->value);
        $this->assertEquals("USD", $monetaryBaseAmount->currencyCode);
        $this->assertEquals("23.30", $monetaryQuoteAmount1->value);
        $this->assertEquals("CAD", $monetaryQuoteAmount1->currencyCode);
        $this->assertEquals("0.997316360864", $response->quotes[0]->exchangeRate);
        $this->assertEquals("0.01", $response->quotes[0]->tradeRate);
        $this->assertEquals("2021-06-16T02:00:00.000000Z", $response->quotes[0]->expiresAt);
        $this->assertEquals("2021-06-16T00:00:00.000000Z", $response->quotes[0]->refreshesAt);
    }

    public function testExchangeRateQuoteAPIwithVariableInputsCanReturnParsableErrors()
    {
        Braintree\Configuration::environment('development');
        $gateway = new Braintree\Gateway(Braintree\Configuration::$global);

        $variables = [
                "quotes" => [
                    "baseCurrency" => "USD",
                    "quoteCurrency" => "EURC",
                    "baseAmount" => "12.19",
                    "markup" => "1.89",
                ],
            ];
        $request = ExchangeRateQuoteRequest::factory($variables);
        $response = $gateway->exchangeRateQuote()->generate($request);

        $this->assertTrue(strpos($response, 'invalid value') !== false);
    }

    public function testExchangeRateQuotewithGraphQLQuoteCurrencyValidationError()
    {
        Braintree\Configuration::environment('development');
        $gateway = new Braintree\Gateway(Braintree\Configuration::$global);
        $quote1 = ExchangeRateQuoteInput::factory([
            "baseCurrency" => "USD",
            "baseAmount" => "12.19",
            "markup" => "12.14",
        ]);
        $quote2 = ExchangeRateQuoteInput::factory([
            "baseCurrency" => "EUR",
            "quoteCurrency" => "CAD",
            "baseAmount" => "15.16",
            "markup" => "2.64",
        ]);
        $variables = [
            "quotes" => [$quote1, $quote2],
        ];
        $request = ExchangeRateQuoteRequest::factory($variables);
        $response = $gateway->exchangeRateQuote()->generate($request);

        $this->assertTrue(strpos($response, 'quoteCurrency') !== false);
    }

    public function testExchangeRateQuotewithGraphQLBaseCurrencyValidationError()
    {
        Braintree\Configuration::environment('development');
        $gateway = new Braintree\Gateway(Braintree\Configuration::$global);
        $quote1 = ExchangeRateQuoteInput::factory([
            "quoteCurrency" => "USD",
            "baseAmount" => "12.19",
            "markup" => "12.14",
        ]);
        $quote2 = ExchangeRateQuoteInput::factory([
            "baseCurrency" => "EUR",
            "quoteCurrency" => "CAD",
            "baseAmount" => "15.16",
            "markup" => "2.64",
        ]);
        $variables = [
            "quotes" => [$quote1, $quote2],
        ];
        $request = ExchangeRateQuoteRequest::factory($variables);
        $response = $gateway->exchangeRateQuote()->generate($request);

        $this->assertTrue(strpos($response, 'baseCurrency') !== false);
    }
    public function testExchangeRateQuotewithGraphQLBaseAndQuoteCurrencyMissing()
    {
        Braintree\Configuration::environment('development');
        $gateway = new Braintree\Gateway(Braintree\Configuration::$global);
        $quote1 = ExchangeRateQuoteInput::factory([
            "baseAmount" => "12.19",
            "markup" => "12.14",
        ]);
        $quote2 = ExchangeRateQuoteInput::factory([
            "baseCurrency" => "EUR",
            "quoteCurrency" => "CAD",
            "baseAmount" => "15.16",
            "markup" => "2.64",
        ]);
        $variables = [
            "quotes" => [$quote1, $quote2],
        ];
        $request = ExchangeRateQuoteRequest::factory($variables);
        $response = $gateway->exchangeRateQuote()->generate($request);

        $this->assertTrue(strpos($response, 'baseCurrency') !== false);
    }
}
