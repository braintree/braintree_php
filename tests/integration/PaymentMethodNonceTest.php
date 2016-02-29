<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PaymentMethodNonceTest extends Setup
{
    public function testCreate_fromPaymentMethodToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        ])->creditCard;

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

    public function testFind_exposesThreeDSecureInfo()
    {
        $nonce = Braintree\PaymentMethodNonce::find('threedsecurednonce');
        $info = $nonce->threeDSecureInfo;

        $this->assertEquals('threedsecurednonce', $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertEquals('Y', $info->enrolled);
        $this->assertEquals('authenticate_successful', $info->status);
        $this->assertTrue($info->liabilityShifted);
        $this->assertTrue($info->liabilityShiftPossible);
    }

    public function testFind_exposesNullThreeDSecureInfoIfNoneExists()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            "creditCard" => [
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ]
        ]);

        $foundNonce = Braintree\PaymentMethodNonce::find($nonce);
        $info = $foundNonce->threeDSecureInfo;

        $this->assertEquals($nonce, $foundNonce->nonce);
        $this->assertNull($info);
    }

    public function testFind_nonExistantNonce()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        Braintree\PaymentMethodNonce::find('not_a_nonce');
    }
}
