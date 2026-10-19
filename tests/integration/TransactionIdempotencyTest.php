<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class TransactionIdempotencyTest extends Setup
{
    public function testSaleWithApiRequestKeyReturnsOriginalTransactionOnDuplicateRequest()
    {
        $apiRequestKey = 'idempotency-key-' . rand(0, 1000000);

        $transactionParams = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'apiRequestKey' => $apiRequestKey,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result1 = Braintree\Transaction::sale($transactionParams);
        $this->assertTrue($result1->success);
        $transaction1 = $result1->transaction;
        $this->assertNotNull($transaction1->id);

        $result2 = Braintree\Transaction::sale($transactionParams);
        $this->assertTrue($result2->success);
        $transaction2 = $result2->transaction;

        $this->assertEquals($transaction1->status, $transaction2->status);
        $this->assertEquals($transaction1->id, $transaction2->id);
    }

    public function testSaleWithApiRequestKeyFailsWhenDifferentRequestUsedWithSameKey()
    {
        $apiRequestKey = 'idempotency-key-' . rand(0, 1000000);

        $transactionParams1 = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'apiRequestKey' => $apiRequestKey,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result1 = Braintree\Transaction::sale($transactionParams1);
        $this->assertTrue($result1->success);

        $transactionParams2 = [
            'amount' => '200.00',
            'apiRequestKey' => $apiRequestKey,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result2 = Braintree\Transaction::sale($transactionParams2);

        $this->assertFalse($result2->success);
        $this->assertNotNull($result2->errors);
        $errors = $result2->errors->deepAll();
        $this->assertTrue(count($errors) > 0);
        $this->assertEquals(Braintree\Error\Codes::API_REQUEST_KEY_CAN_BE_REUSED_ONLY_WITH_THE_SAME_REQUEST, $errors[0]->code);
    }

    public function testSubmitForPartialSettlementWithApiRequestKeyReturnsOriginalOnDuplicateRequest()
    {
        $apiRequestKey = 'partial-settlement-idempotency-key-' . rand(0, 1000000);

        $saleRequest = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $saleResult = Braintree\Transaction::sale($saleRequest);
        $this->assertTrue($saleResult->success);
        $transactionId = $saleResult->transaction->id;

        $partialAmount = '50.00';
        $partialSettlementRequest = [
            'apiRequestKey' => $apiRequestKey
        ];

        $partialSettlementResult1 = Braintree\Transaction::submitForPartialSettlement(
            $transactionId,
            $partialAmount,
            $partialSettlementRequest
        );
        $this->assertTrue($partialSettlementResult1->success);
        $partialSettlementTransaction1 = $partialSettlementResult1->transaction;
        $this->assertEquals($partialAmount, $partialSettlementTransaction1->amount);
        $this->assertNotNull($partialSettlementTransaction1->id);

        $partialSettlementResult2 = Braintree\Transaction::submitForPartialSettlement(
            $transactionId,
            $partialAmount,
            $partialSettlementRequest
        );
        $this->assertTrue($partialSettlementResult2->success);
        $partialSettlementTransaction2 = $partialSettlementResult2->transaction;

        $this->assertEquals($partialSettlementTransaction1->id, $partialSettlementTransaction2->id);
        $this->assertEquals($partialSettlementTransaction1->amount, $partialSettlementTransaction2->amount);
    }

    public function testSubmitForSettlementWithApiRequestKeyReturnsOriginalOnDuplicateRequest()
    {
        $apiRequestKey = 'settlement-idempotency-key-' . rand(0, 1000000);

        $saleRequest = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $saleResult = Braintree\Transaction::sale($saleRequest);
        $this->assertTrue($saleResult->success);
        $transactionId = $saleResult->transaction->id;
        $originalAmount = $saleResult->transaction->amount;

        $settlementRequest = [
            'apiRequestKey' => $apiRequestKey
        ];

        $settlementResult1 = Braintree\Transaction::submitForSettlement(
            $transactionId,
            null,
            $settlementRequest
        );
        $this->assertTrue($settlementResult1->success);
        $settlementTransaction1 = $settlementResult1->transaction;
        $this->assertEquals($originalAmount, $settlementTransaction1->amount);
        $this->assertNotNull($settlementTransaction1->id);

        $settlementResult2 = Braintree\Transaction::submitForSettlement(
            $transactionId,
            null,
            $settlementRequest
        );
        $this->assertTrue($settlementResult2->success);
        $settlementTransaction2 = $settlementResult2->transaction;

        $this->assertEquals($settlementTransaction1->id, $settlementTransaction2->id);
        $this->assertEquals($settlementTransaction1->amount, $settlementTransaction2->amount);
    }

    public function testVoidWithApiRequestKeyReturnsOriginalVoidOnDuplicateRequest()
    {
        $apiRequestKey = 'void-idempotency-key-' . rand(0, 1000000);

        $saleRequest = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $saleResult = Braintree\Transaction::sale($saleRequest);
        $this->assertTrue($saleResult->success);
        $transactionId = $saleResult->transaction->id;

        $voidRequest = [
            'apiRequestKey' => $apiRequestKey
        ];

        $voidResult1 = Braintree\Transaction::void($transactionId, $voidRequest);
        $this->assertTrue($voidResult1->success);
        $voidedTransaction1 = $voidResult1->transaction;
        $this->assertEquals(Braintree\Transaction::VOIDED, $voidedTransaction1->status);

        $voidResult2 = Braintree\Transaction::void($transactionId, $voidRequest);
        $this->assertTrue($voidResult2->success);
        $voidedTransaction2 = $voidResult2->transaction;

        $this->assertEquals($voidedTransaction1->id, $voidedTransaction2->id);
        $this->assertEquals($voidedTransaction1->status, $voidedTransaction2->status);
        $this->assertEquals(Braintree\Transaction::VOIDED, $voidedTransaction2->status);
    }

    public function testReverseWithApiRequestKeyReturnsOriginalReverseOnDuplicateRequest()
    {
        $apiRequestKey = 'reverse-idempotency-key-' . rand(0, 1000000);

        $saleRequest = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $saleResult = Braintree\Transaction::sale($saleRequest);
        $this->assertTrue($saleResult->success);
        $transactionId = $saleResult->transaction->id;

        $reverseRequest = [
            'apiRequestKey' => $apiRequestKey
        ];

        $reverseResult1 = Braintree\Transaction::reverse($transactionId, $reverseRequest);
        $this->assertTrue($reverseResult1->success);

        $reverseResult2 = Braintree\Transaction::reverse($transactionId, $reverseRequest);
        $this->assertTrue($reverseResult2->success);
        $this->assertEquals($reverseResult1->transaction->id, $reverseResult2->transaction->id);
        $this->assertEquals(Braintree\Transaction::VOIDED, $reverseResult2->transaction->status);
    }

    public function testRefundWithApiRequestKeyReturnsOriginalRefundOnDuplicateRequest()
    {
        $apiRequestKey = 'refund-idempotency-key-' . rand(0, 1000000);

        $saleRequest = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ],
            'options' => [
                'submitForSettlement' => true
            ]
        ];

        $saleResult = Braintree\Transaction::sale($saleRequest);
        $this->assertTrue($saleResult->success);
        $transactionId = $saleResult->transaction->id;

        $settledTransaction = Braintree\Test\Transaction::settle($transactionId);
        $this->assertEquals(Braintree\Transaction::SETTLED, $settledTransaction->status);

        $refundRequest = [
            'apiRequestKey' => $apiRequestKey
        ];

        $refundResult1 = Braintree\Transaction::refund($transactionId, $refundRequest);
        $this->assertTrue($refundResult1->success);
        $refundTransaction1 = $refundResult1->transaction;
        $this->assertEquals(Braintree\Transaction::CREDIT, $refundTransaction1->type);
        $this->assertNotNull($refundTransaction1->id);

        $refundResult2 = Braintree\Transaction::refund($transactionId, $refundRequest);
        $this->assertTrue($refundResult2->success);
        $refundTransaction2 = $refundResult2->transaction;

        $this->assertEquals($refundTransaction1->id, $refundTransaction2->id);
        $this->assertEquals($refundTransaction1->type, $refundTransaction2->type);
    }

    public function testSameSalesWithDifferentApiRequestKey()
    {
        $apiRequestKey1 = 'idempotency-key-' . rand(0, 1000000);

        $transactionParams1 = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'apiRequestKey' => $apiRequestKey1,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result1 = Braintree\Transaction::sale($transactionParams1);
        $this->assertTrue($result1->success);
        $transaction1 = $result1->transaction;
        $this->assertNotNull($transaction1->id);

        $apiRequestKey2 = 'idempotency-key-' . rand(0, 1000000);
        $transactionParams2 = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'apiRequestKey' => $apiRequestKey2,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result2 = Braintree\Transaction::sale($transactionParams2);
        $this->assertTrue($result2->success);
        $transaction2 = $result2->transaction;

        $this->assertNotEquals($transaction1->id, $transaction2->id);
    }

    public function testSaleWithApiRequestKeyFailsWhenApiRequestKeyIsTooBig()
    {
        $transactionParams1 = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'apiRequestKey' => str_repeat('x', 255),
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result1 = Braintree\Transaction::sale($transactionParams1);
        $this->assertTrue($result1->success);

        $transactionParams2 = [
            'amount' => '200.00',
            'apiRequestKey' => str_repeat('x', 256),
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $result2 = Braintree\Transaction::sale($transactionParams2);

        $this->assertFalse($result2->success);
        $this->assertNotNull($result2->errors);
        $errors = $result2->errors->deepAll();
        $this->assertTrue(count($errors) > 0);
        $this->assertEquals(Braintree\Error\Codes::API_REQUEST_KEY_TOO_LONG, $errors[0]->code);
    }

    public function testCreditWithApiRequestKeyReturnsOriginalOnDuplicateRequest()
    {
        $apiRequestKey = 'credit-idempotency-key-' . rand(0, 1000000);

        $transactionParams = [
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'apiRequestKey' => $apiRequestKey,
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2035'
            ]
        ];

        $creditResult1 = Braintree\Transaction::credit($transactionParams);
        $this->assertTrue($creditResult1->success);
        $creditTransaction1 = $creditResult1->transaction;
        $this->assertEquals(Braintree\Transaction::CREDIT, $creditTransaction1->type);
        $this->assertNotNull($creditTransaction1->id);

        $creditResult2 = Braintree\Transaction::credit($transactionParams);
        $this->assertTrue($creditResult2->success);
        $creditTransaction2 = $creditResult2->transaction;

        $this->assertEquals($creditTransaction1->id, $creditTransaction2->id);
        $this->assertEquals($creditTransaction1->type, $creditTransaction2->type);
    }
}
