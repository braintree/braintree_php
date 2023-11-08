<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class MetaCheckoutCardTest extends Setup
{
    public function testFactoryCreatesInstanceWithDefaultAttributes()
    {
        $defaultAttributes = [
            'bin' => '',
            'expirationMonth' => '',
            'expirationYear' => '',
            'last4' => '',
        ];

        $creditCard = Braintree\MetaCheckoutCard::factory([]);

        $this->assertInstanceOf(Braintree\MetaCheckoutCard::class, $creditCard);
        $this->assertEquals($defaultAttributes['bin'], $creditCard->bin);
        $this->assertEquals($defaultAttributes['expirationMonth'], $creditCard->expirationMonth);
        $this->assertEquals($defaultAttributes['expirationYear'], $creditCard->expirationYear);
        $this->assertEquals($defaultAttributes['last4'], $creditCard->last4);
    }

    public function testFactoryCreatesInstanceWithProvidedAttributes()
    {
        $attributes = [
            'bin' => '1234',
            'expirationMonth' => '12',
            'expirationYear' => '25',
            'last4' => '5678',
        ];

        $creditCard = Braintree\MetaCheckoutCard::factory($attributes);

        $this->assertInstanceOf(Braintree\MetaCheckoutCard::class, $creditCard);
        $this->assertEquals($attributes['bin'], $creditCard->bin);
        $this->assertEquals($attributes['expirationMonth'], $creditCard->expirationMonth);
        $this->assertEquals($attributes['expirationYear'], $creditCard->expirationYear);
        $this->assertEquals($attributes['last4'], $creditCard->last4);
    }
}
