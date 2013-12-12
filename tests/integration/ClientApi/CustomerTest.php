<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_ClientApiCustomerTest extends PHPUnit_Framework_TestCase
{
    function testCreateCustomerWithCardUsingNonce()
    {
        $nonce = Braintree_HttpClientApi::nonce_for_new_card(array(
            "creditCard" => array(
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ),
            "share" => true
        ));

        $result = Braintree_Customer::create(array(
            'creditCard' => array(
                'paymentMethodNonce' => $nonce
            )
        ));

        $this->assertTrue($result->success);
        $this->assertSame("411111", $result->customer->creditCards[0]->bin);
        $this->assertSame("1111", $result->customer->creditCards[0]->last4);
    }
}
