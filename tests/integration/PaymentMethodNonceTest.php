<?php namespace Braintree\Tests\Integration;

use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\PaymentMethodNonce;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class PaymentMethodNonceTest extends \PHPUnit_Framework_TestCase
{
    function testCreate_fromPaymentMethodToken()
    {
        $customer = Customer::createNoValidate();
        $card = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $result = PaymentMethodNonce::create($card->token);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethodNonce);
        $this->assertNotNull($result->paymentMethodNonce->nonce);
    }

    function testCreate_fromNonExistentPaymentMethodToken()
    {
        $this->setExpectedException('Exception_NotFound');
        PaymentMethodNonce::create('not_a_token');
    }

    function testFind_exposesThreeDSecureInfo()
    {
        $nonce = PaymentMethodNonce::find('threedsecurednonce');
        $info = $nonce->threeDSecureInfo;

        $this->assertEquals('threedsecurednonce', $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertEquals('Y', $info->enrolled);
        $this->assertEquals('authenticate_successful', $info->status);
        $this->assertTrue($info->liabilityShifted);
        $this->assertTrue($info->liabilityShiftPossible);
    }

    function testFind_exposesNullThreeDSecureInfoIfNoneExists()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            )
        ));

        $foundNonce = PaymentMethodNonce::find($nonce);
        $info = $foundNonce->threeDSecureInfo;

        $this->assertEquals($nonce, $foundNonce->nonce);
        $this->assertNull($info);
    }

    function testFind_nonExistantNonce()
    {
        $this->setExpectedException('Exception_NotFound');
        PaymentMethodNonce::create('not_a_nonce');
    }
}
