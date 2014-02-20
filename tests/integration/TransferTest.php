<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DisbursementExceptionTest extends PHPUnit_Framework_TestCase
{
    function testMerchantAccount()
    {
        $disbursementException = Braintree_DisbursementException::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $merchantAccount = $disbursementException->merchantAccount();

        $this->assertNotNull($merchantAccount);
        $this->assertEquals($merchantAccount->id, 'sandbox_sub_merchant_account');
    }

    function testMerchantAccountIsMemoized()
    {
        $disbursementException = Braintree_DisbursementException::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $firstMerchantAccount = $disbursementException->merchantAccount();
        $disbursementException->merchantAccountId = "non existent";

        $this->assertEquals($firstMerchantAccount, $disbursementException->merchantAccount());
    }

    function testTransactions()
    {
        $disbursementException = Braintree_DisbursementException::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $transactions = $disbursementException->transactions();

        $this->assertNotNull($transactions);
        $this->assertEquals(sizeOf($transactions), 1);
        $this->assertEquals($transactions->firstItem()->id, 'sub_merchant_transaction');
    }
}
