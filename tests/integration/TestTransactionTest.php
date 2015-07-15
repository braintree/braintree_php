<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TestTransactionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @after
     */
    public function tearDownResetBraintreeEnvironment()
    {
        Braintree_Configuration::environment('development');
    }

    function testSettle()
    {
        $transaction = Braintree_Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree_Test_Transaction::settle($transaction->id);

        $this->assertEquals('settled', $transaction->status);
    }

    function testSettlementConfirmed()
    {
         $transaction = Braintree_Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree_Test_Transaction::settlementConfirm($transaction->id);

        $this->assertEquals('settlement_confirmed', $transaction->status);
    }

    function testSettlementDeclined()
    {
         $transaction = Braintree_Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree_Test_Transaction::settlementDecline($transaction->id);

        $this->assertEquals('settlement_declined', $transaction->status);
    }

    function testSettlementPending()
    {
         $transaction = Braintree_Transaction::saleNoValidate([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ],
            'options' => ['submitForSettlement' => true]
        ]);

        $transaction = Braintree_Test_Transaction::settlementPending($transaction->id);

        $this->assertEquals('settlement_pending', $transaction->status);
    }

    /**
     * @expectedException Braintree_Exception_TestOperationPerformedInProduction
     */
    function testThrowingExceptionWhenProduction()
    {
        Braintree_Configuration::environment('production');

        Braintree_Test_Transaction::settle('transactionId');
    }
}
