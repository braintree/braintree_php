<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    function testToString()
    {
        $disbursement = Braintree_Disbursement::factory(array(
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
            "disbursementDate" => "2013-04-10",
            "followUpAction" => "update",
            "retry" => false,
            "success" => true
        ));

       $this->assertEquals((string) $disbursement, 'Braintree_Disbursement[id=123456, merchantAccount=id=sandbox_sub_merchant_account, masterMerchantAccount=id=sandbox_master_merchant_account, status=active, status=active, exceptionMessage=invalid_account_number, amount=100.00, disbursementDate=2013-04-10, followUpAction=update, retry=, success=1, transactionIds=0=sub_merchant_transaction]');
    }
}
