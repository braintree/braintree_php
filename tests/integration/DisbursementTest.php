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
        $disbursement = Braintree\Disbursement::factory(array(
            "id" => "123456",
            "merchantAccount" => array(
                "id" => "sandbox_sub_merchant_account",
                "masterMerchantAccount" => array(
                    "id" => "sandbox_master_merchant_account",
                    "status" => "active"
                    ),
                "status" => "active"
                ),
            "transactionIds" => array("sub_merchant_transaction"),
            "exceptionMessage" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update",
            "retry" => false,
            "success" => false
        ));

        $transactions = $disbursement->transactions();

        $this->assertNotNull($transactions);
        $this->assertEquals(sizeOf($transactions), 1);
        $this->assertEquals($transactions->firstItem()->id, 'sub_merchant_transaction');
    }
}
