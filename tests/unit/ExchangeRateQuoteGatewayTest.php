<?php

namespace Test\unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ExchangeRateQuoteGatewayTest extends Setup
{
    public function testCreateSignature()
    {
        $expected = [
            ['quotes' => ['baseCurrency' ,
                'quoteCurrency',
                'baseAmount',
                'markup'
            ],]
        ];

        $this->assertEquals($expected, Braintree\ExchangeRateQuoteGateway::createSignature());
    }
}
