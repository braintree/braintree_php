<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class DisbursementTest extends Setup
{
    public function testToString()
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

       $this->assertEquals((string) $disbursement, 'Braintree\Disbursement[id=123456, merchantAccountDetails=id=sandbox_sub_merchant_account, masterMerchantAccount=id=sandbox_master_merchant_account, status=active, status=active, exceptionMessage=invalid_account_number, amount=100.00, disbursementDate=Wednesday, 10-Apr-13 00:00:00 UTC, followUpAction=update, retry=, success=, transactionIds=0=sub_merchant_transaction]');
    }
}
