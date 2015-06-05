<?php namespace Braintree\Tests\Integration;

use Braintree\Disbursement;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class DisbursementTest extends \PHPUnit_Framework_TestCase
{
    function testTransactions()
    {
        $disbursement = Disbursement::factory(array(
            "id"               => "123456",
            "merchantAccount"  => array(
                "id"                    => "sandbox_sub_merchant_account",
                "masterMerchantAccount" => array(
                    "id"     => "sandbox_master_merchant_account",
                    "status" => "active"
                ),
                "status"                => "active"
            ),
            "transactionIds"   => array("sub_merchant_transaction"),
            "exceptionMessage" => "invalid_account_number",
            "amount"           => "100.00",
            "disbursementDate" => new \DateTime("2013-04-10"),
            "followUpAction"   => "update",
            "retry"            => false,
            "success"          => false
        ));

        $transactions = $disbursement->transactions();

        $this->assertNotNull($transactions);
        $this->assertEquals(sizeOf($transactions), 1);
        $this->assertEquals($transactions->firstItem()->id, 'sub_merchant_transaction');
    }
}
