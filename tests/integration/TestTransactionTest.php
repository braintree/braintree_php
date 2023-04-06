<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class TestTransactionTest extends Setup
{
    public function setUp(): void
    {
        parent::setUp();

        Braintree\Configuration::environment('development');
    }

    /**
     * @after
     */
    public function tearDownResetBraintreeEnvironment()
    {
        Braintree\Configuration::environment('development');
    }

    /**
     * @expectException Exception\TestOperationPerformedInProduction
     */
    public function testThrowingExceptionWhenProduction()
    {
        Braintree\Configuration::environment('production');

        $this->expectException('Braintree\Exception\TestOperationPerformedInProduction');

        Braintree\Test\Transaction::settle('foo');
    }

    public function testSettle()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree\Test\Transaction::settle($transaction->id);

        $this->assertEquals(Braintree\Transaction::SETTLED, $transaction->status);
    }

    public function testSettlementConfirmed()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree\Test\Transaction::settlementConfirm($transaction->id);

        $this->assertEquals(Braintree\Transaction::SETTLEMENT_CONFIRMED, $transaction->status);
    }

    public function testSettlementDeclined()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree\Test\Transaction::settlementDecline($transaction->id);

        $this->assertEquals(Braintree\Transaction::SETTLEMENT_DECLINED, $transaction->status);
    }

    public function testSettlementPending()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree\Test\Transaction::settlementPending($transaction->id);

        $this->assertEquals(Braintree\Transaction::SETTLEMENT_PENDING, $transaction->status);
    }

    public function testValidationError()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree\Test\Transaction::settle($transaction->id);

        $this->assertEquals(Braintree\Transaction::SETTLED, $transaction->status);

        $result = Braintree\Test\Transaction::settle($transaction->id);

        $this->assertFalse($result->success);

        $errorCode = $result->errors->forKey('transaction')->onAttribute('base')[0]->code;

        $this->assertEquals(Braintree\Error\Codes::TRANSACTION_CANNOT_SIMULATE_SETTLEMENT, $errorCode);
    }
}
