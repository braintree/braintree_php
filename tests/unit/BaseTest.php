<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class BaseTest extends Setup
{
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
        'channel' => 'sonic',
      ]);
      $detailsArray = $transaction->toArray();
      $this->assertIsArray($detailsArray);
      $this->assertArrayHasKey('creditCard', $detailsArray);
      $this->assertIsArray($detailsArray["creditCard"]);
      $this->assertEquals('05', $detailsArray["creditCard"]["expirationMonth"]);
      $this->assertEquals('2010', $detailsArray["creditCard"]["expirationYear"]);
      $this->assertEquals('510510', $detailsArray["creditCard"]["bin"]);
      $this->assertEquals('5100', $detailsArray["creditCard"]["last4"]);
      $this->assertEquals('MasterCard', $detailsArray["creditCard"]["cardType"]);
      $this->assertArrayHasKey('channel', $detailsArray);
      $this->assertEquals('sonic', $detailsArray["channel"]);
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

    public function test__toString()
    {
      $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
        'id' => 'foo',
        'type' => 'sale',
        'amount' => '10.00',
        'status' => 'authorized',
        'createdAt' => '1/1/11',
        'customer' => [
          'id' => 'bar',
        ],
      ]);
      $stringified = $transaction->__toString();
      $this->assertEquals('Braintree\Transaction[id=foo, type=sale, amount=10.00, status=authorized, createdAt=1/1/11, creditCardDetails=Braintree\Transaction\CreditCardDetails[expirationMonth=05, expirationYear=2010, bin=510510, last4=5100, cardType=MasterCard, expirationDate=05/2010, maskedNumber=510510******5100], customerDetails=Braintree\Transaction\CustomerDetails[id=bar]]', $stringified);
    }

    public function test__isset()
    {
      $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
        'channel' => 'sonic',
      ]);
      $this->assertTrue(isset($transaction->channel));
      $this->assertFalse(empty($transaction->channel));


      $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
        'channel' => false,
      ]);
      $this->assertTrue(isset($transaction->channel));
      $this->assertTrue(empty($transaction->channel));


      $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
        'channel' => null,
      ]);
      $this->assertFalse(isset($transaction->channel));
      $this->assertTrue(empty($transaction->channel));


      $transaction = Braintree\Transaction::factory([
        'creditCard' => [
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard',
        ],
      ]);
      $this->assertFalse(isset($transaction->channel));
      $this->assertTrue(empty($transaction->channel));

    }
}
