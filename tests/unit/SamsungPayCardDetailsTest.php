<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree\Transaction\SamsungPayCardDetails;

class SamsungPayCardDetailsTest extends Setup
{
    public function testConstructor_setsExpirationDate()
    {
        $details = new SamsungPayCardDetails([
            'expirationMonth' => '12',
            'expirationYear' => '2025',
            'bin' => '411111',
            'last4' => '1111',
        ]);

        $this->assertEquals('12/2025', $details->expirationDate);
    }

    public function testConstructor_setsMaskedNumber()
    {
        $details = new SamsungPayCardDetails([
            'expirationMonth' => '12',
            'expirationYear' => '2025',
            'bin' => '411111',
            'last4' => '1111',
        ]);

        $this->assertEquals('411111******1111', $details->maskedNumber);
    }

    public function testConstructor_preservesOtherAttributes()
    {
        $details = new SamsungPayCardDetails([
            'expirationMonth' => '01',
            'expirationYear' => '2030',
            'bin' => '555555',
            'last4' => '4444',
            'cardType' => 'Mastercard',
        ]);

        $this->assertEquals('Mastercard', $details->cardType);
    }
}
