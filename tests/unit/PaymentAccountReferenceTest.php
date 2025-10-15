<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PaymentAccountReferenceTest extends Setup
{
    public function test_creditCardDetails_paymentAccountReference()
    {
        $details = new Braintree\Transaction\CreditCardDetails([
            'bin' => '123456',
            'last4' => '6789',
            'cardType' => 'Visa',
            'expirationMonth' => '05',
            'expirationYear' => '2025',
            'paymentAccountReference' => 'V0010013019339005665779448477'
        ]);

        $this->assertEquals('V0010013019339005665779448477', $details->paymentAccountReference);
    }

    public function test_applePayCardDetails_paymentAccountReference()
    {
        $details = new Braintree\Transaction\ApplePayCardDetails([
            'bin' => '123456',
            'last4' => '6789',
            'cardType' => 'Visa',
            'paymentAccountReference' => 'V0010013019339005665779448477'
        ]);

        $this->assertEquals('V0010013019339005665779448477', $details->paymentAccountReference);
    }

    public function test_googlePayCardDetails_paymentAccountReference()
    {
        $details = new Braintree\Transaction\GooglePayCardDetails([
            'virtualCardLast4' => '6789',
            'virtualCardType' => 'Visa',
            'paymentAccountReference' => 'V0010013019339005665779448477'
        ]);

        $this->assertEquals('V0010013019339005665779448477', $details->paymentAccountReference);
    }
}
