<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DisbursementTest extends PHPUnit_Framework_TestCase
{
    function testMerchantAccount()
    {
        $disbursement = Braintree_Disbursement::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $merchantAccount = $disbursement->merchantAccount();

        $this->assertNotNull($merchantAccount);
        $this->assertEquals($merchantAccount->id, 'sandbox_sub_merchant_account');
    }

    function testMerchantAccountIsMemoized()
    {
        $disbursement = Braintree_Disbursement::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $firstMerchantAccount = $disbursement->merchantAccount();
        $disbursement->merchantAccountId = "non existent";

        $this->assertEquals($firstMerchantAccount, $disbursement->merchantAccount());
    }

    function testTransactions()
    {
        $disbursement = Braintree_Disbursement::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $transactions = $disbursement->transactions();

        $this->assertNotNull($transactions);
        $this->assertEquals(sizeOf($transactions), 1);
        $this->assertEquals($transactions->firstItem()->id, 'sub_merchant_transaction');
    }
}
