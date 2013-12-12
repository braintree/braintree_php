<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_ClientApiCreditCardTest extends PHPUnit_Framework_TestCase
{
    function testAddCardToExistingCustomerUsingNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonce_for_new_card(array(
            "credit_card" => array(
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ),
            "share" => true
        ));

        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame("411111", $result->creditCard->bin);
        $this->assertSame("1111", $result->creditCard->last4);
    }
}
