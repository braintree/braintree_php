<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    function testToString()
    {
        $disbursement = Braintree_Disbursement::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

       $this->assertEquals((string) $disbursement, 'Braintree_Disbursement[id=123456, merchantAccountId=sandbox_sub_merchant_account, message=invalid_account_number, amount=100.00, disbursementDate=Wednesday, 10-Apr-13 00:00:00 UTC, followUpAction=update]');
    }
}
