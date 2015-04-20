<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';
require_once realpath(dirname(__FILE__)).'/HttpClientApi.php';

class Braintree_PaymentMethodNonceTest extends PHPUnit_Framework_TestCase
{
    public function testCreate_fromPaymentMethodToken()
    {
        $customer = Braintree_Customer::createNoValidate();
        $card = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        ))->creditCard;

        $result = Braintree_PaymentMethodNonce::create($card->token);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethodNonce);
        $this->assertNotNull($result->paymentMethodNonce->nonce);
    }

    public function testCreate_fromNonExistentPaymentMethodToken()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethodNonce::create('not_a_token');
    }
}
