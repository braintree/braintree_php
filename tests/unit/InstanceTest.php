<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class InstanceTest extends Setup
{
    public function test__isset_whenSetReturnsTrue()
    {
        $transaction = Braintree\Transaction::factory([
            'creditCard' => [
                'expirationMonth' => '05',
                'expirationYear' => '2010',
                'bin' => '510510',
                'last4' => '5100',
                'cardType' => 'MasterCard',
            ],
        ]);
        $this->assertEquals('MasterCard', $transaction->creditCardDetails->cardType);
        $this->assertFalse(empty($transaction->creditCardDetails->cardType));
        $this->assertTrue(isset($transaction->creditCardDetails->cardType));

        $transaction = Braintree\Transaction::factory([
            'creditCard' => [
                'expirationMonth' => '05',
                'expirationYear' => '2010',
                'bin' => '510510',
                'last4' => '5100',
                'cardType' => false,
            ],
        ]);
        $this->assertTrue(empty($transaction->creditCardDetails->cardType));
        $this->assertTrue(isset($transaction->creditCardDetails->cardType));
    }

    public function test__isset_whenNotSetReturnsFalse()
    {
        $transaction = Braintree\Transaction::factory([
            'creditCard' => [
                'expirationMonth' => '05',
                'expirationYear' => '2010',
                'bin' => '510510',
                'last4' => '5100',
            ],
        ]);
        $this->assertTrue(empty($transaction->creditCardDetails->cardType));
        $this->assertFalse(isset($transaction->creditCardDetails->cardType));
    }

    public function test__isset_whenSetToNullReturnsFalse()
    {
        $transaction = Braintree\Transaction::factory([
            'creditCard' => [
                'expirationMonth' => '05',
                'expirationYear' => '2010',
                'bin' => '510510',
                'last4' => '5100',
                'cardType' => null,
            ],
        ]);
        $this->assertTrue(empty($transaction->creditCardDetails->cardType));
        $this->assertFalse(isset($transaction->creditCardDetails->cardType));
    }

    public function testToArray()
    {
        $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
        ]);
        $detailsArray = $transaction->creditCardDetails->toArray();
        $this->assertEquals('MasterCard', $detailsArray["cardType"]);
    }

    public function testJsonSerialize()
    {
        $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
        ]);
        $serialized = $transaction->creditCardDetails->jsonSerialize();
        $this->assertEquals('MasterCard', $serialized["cardType"]);
    }
}
