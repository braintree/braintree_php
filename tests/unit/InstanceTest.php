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

    public function testStatusHistoryJsonEncode()
    {
        $transaction = Braintree\Transaction::factory([
          'statusHistory' => [
            [
              'timestamp' => new \DateTime('2025-10-23 18:21:37'),
              'status' => 'authorized',
              'amount' => '15.00',
              'user' => 'username_here',
              'transactionSource' => 'api'
            ]
          ]
        ]);
        $json = json_encode($transaction);
        $decoded = json_decode($json, true);

        $this->assertArrayHasKey('timestamp', $decoded['statusHistory'][0]);
        $this->assertIsArray($decoded['statusHistory'][0]['timestamp']);
        $this->assertEquals('2025-10-23 18:21:37.000000', $decoded['statusHistory'][0]['timestamp']['date']);
        $this->assertEquals('authorized', $decoded['statusHistory'][0]['status']);
        $this->assertEquals('15.00', $decoded['statusHistory'][0]['amount']);
        $this->assertEquals('username_here', $decoded['statusHistory'][0]['user']);
        $this->assertEquals('api', $decoded['statusHistory'][0]['transactionSource']);
    }
}
