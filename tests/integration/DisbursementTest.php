<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class DisbursementTest extends Setup
{
    public function testTransactions()
    {
        $disbursement = Braintree\Disbursement::factory([
            "id" => "123456",
            "merchantAccount" => [
                "id" => "ma_card_processor_brazil",
                "status" => "active"
                ],
            "transactionIds" => ["transaction_with_installments_and_adjustments"],
            "exceptionMessage" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update",
            "retry" => false,
            "success" => false
        ]);

        $transactions = $disbursement->transactions();

        $this->assertNotNull($transactions);
        $this->assertEquals($transactions->maximumCount(), 1);
    }
}
