<?php
namespace Test\Integration;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class PaymentMethodNonceTest extends Setup
{
    public function testCreate_fromPaymentMethodToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        ))->creditCard;

        $result = Braintree\PaymentMethodNonce::create($card->token);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethodNonce);
        $this->assertNotNull($result->paymentMethodNonce->nonce);
    }

    public function testCreate_fromNonExistentPaymentMethodToken()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        Braintree\PaymentMethodNonce::create('not_a_token');
    }
}
