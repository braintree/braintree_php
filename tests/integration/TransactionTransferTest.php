<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Braintree;
use Test\Setup;

class TransactionTransferTest extends Setup
{
    public function testSaleWithValidAftTransferType()
    {
        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',
            'merchantAccountId' => 'aft_first_data_wallet_transfer',
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '06/2026',
                'cvv' => '123',
            ],
            'transfer' => [
                'type' => 'wallet_transfer',
            ],
        ];

        $result = Braintree\Transaction::sale($transactionParams);

        $this->assertTrue($result->success);
        $this->assertTrue($result->transaction->accountFundingTransaction);
        $this->assertEquals(Braintree\Transaction::AUTHORIZED, $result->transaction->status);
    }

    public function testSaleWithInvalidTransferType()
    {
        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',

            'merchantAccountId' => 'aft_first_data_wallet_transfer',
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '06/2026',
                'cvv' => '123',
            ],
            'transfer' => [
                'type' => 'invalid_transfer',
            ],
        ];

        $result = Braintree\Transaction::sale($transactionParams);

        $this->assertFalse($result->success);
    }
}
