<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransferTest extends PHPUnit_Framework_TestCase
{
    function testMerchantAccount()
    {
        $transfer = Braintree_Transfer::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $merchantAccount = $transfer->merchantAccount();

        $this->assertNotNull($merchantAccount);
        $this->assertEquals($merchantAccount->id, 'sandbox_sub_merchant_account');
    }

<<<<<<< Updated upstream
=======
    function testMerchantAccountIsMemoized()
    {
        $transfer = Braintree_Transfer::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $firstMerchantAccount = $transfer->merchantAccount();
        $transfer->merchantAccountId = "non existent";

        $this->assertEquals($firstMerchantAccount, $transfer->merchantAccount());
    }

>>>>>>> Stashed changes
    function testTransactions()
    {
        $transfer = Braintree_Transfer::factory(array(
            "merchantAccountId" => "sandbox_sub_merchant_account",
            "id" => "123456",
            "message" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update"
        ));

        $transactions = $transfer->transactions();

        $this->assertNotNull($transactions);
        $this->assertEquals(sizeOf($transactions), 1);
        $this->assertEquals($transactions->firstItem()->id, 'sub_merchant_transaction');
    }
}
