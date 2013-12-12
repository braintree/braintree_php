<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_ClientApiTransactionTest extends PHPUnit_Framework_TestCase
{
    function testCreateTransactionUsingNonce()
    {
        $nonce = Braintree_HttpClientApi::nonce_for_new_card(array(
            "creditCard" => array(
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ),
            "share" => true
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('47.00', $transaction->amount);
    }
}
