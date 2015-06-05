<?php namespace Braintree\Tests\Integration;

use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Subscription;
use Braintree\Test\Nonces;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class PayPalAccountTest extends \PHPUnit_Framework_TestCase
{
    function testFind()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $foundPayPalAccount = PayPalAccount::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
        $this->assertNotNull($foundPayPalAccount->imageUrl);
    }

    function testGatewayFind()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $foundPayPalAccount = $gateway->paypalAccount()->find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
        $this->assertNotNull($foundPayPalAccount->imageUrl);
    }

    function testFind_doesNotReturnIncorrectPaymentMethodType()
    {
        $creditCardToken = 'creditCardToken-' . strval(rand());
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'token'          => $creditCardToken
        ));
        $this->assertTrue($result->success);

        $this->setExpectedException('Exception_NotFound');
        PayPalAccount::find($creditCardToken);
    }

    function test_PayPalAccountExposesTimestamps()
    {
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => Nonces::$paypalFuturePayment
        ));
        $this->assertTrue($result->success);

        $foundPayPalAccount = PayPalAccount::find($result->paymentMethod->token);

        $this->assertNotNull($result->paymentMethod->createdAt);
        $this->assertNotNull($result->paymentMethod->updatedAt);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Exception_NotFound');
        PayPalAccount::find('invalid-token');
    }

    function testFind_throwsUsefulErrorMessagesWhenEmpty()
    {
        $this->setExpectedException('\InvalidArgumentException', 'expected paypal account id to be set');
        PayPalAccount::find('');
    }

    function testFind_throwsUsefulErrorMessagesWhenInvalid()
    {
        $this->setExpectedException('\InvalidArgumentException', '@ is an invalid paypal account token');
        PayPalAccount::find('@');
    }

    function testFind_returnsSubscriptionsAssociatedWithAPaypalAccount()
    {
        $customer = Customer::createNoValidate();
        $paymentMethodToken = 'paypal-account-' . strval(rand());

        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token'        => $paymentMethodToken
            )
        ));

        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id
        ));
        $this->assertTrue($result->success);

        $token = $result->paymentMethod->token;
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $subscription1 = Subscription::create(array(
            'paymentMethodToken' => $token,
            'planId'             => $triallessPlan['id']
        ))->subscription;

        $subscription2 = Subscription::create(array(
            'paymentMethodToken' => $token,
            'planId'             => $triallessPlan['id']
        ))->subscription;

        $paypalAccount = PayPalAccount::find($token);
        $getIds = function ($sub) {
            return $sub->id;
        };
        $subIds = array_map($getIds, $paypalAccount->subscriptions);
        $this->assertTrue(in_array($subscription1->id, $subIds));
        $this->assertTrue(in_array($subscription2->id, $subIds));
    }

    function testUpdate()
    {
        $originalToken = 'ORIGINAL_PAYPALToken-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $originalToken
            )
        ));

        $createResult = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($createResult->success);

        $newToken = 'NEW_PAYPALToken-' . strval(rand());
        $updateResult = PayPalAccount::update($originalToken, array(
            'token' => $newToken
        ));

        $this->assertTrue($updateResult->success);
        $this->assertEquals($newToken, $updateResult->paypalAccount->token);

        $this->setExpectedException('Exception_NotFound');
        PayPalAccount::find($originalToken);

    }

    function testUpdateAndMakeDefault()
    {
        $customer = Customer::createNoValidate();

        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($creditCardResult->success);

        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE'
            )
        ));

        $createResult = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($createResult->success);

        $updateResult = PayPalAccount::update($createResult->paymentMethod->token, array(
            'options' => array('makeDefault' => true)
        ));

        $this->assertTrue($updateResult->success);
        $this->assertTrue($updateResult->paypalAccount->isDefault());
    }

    function testUpdate_handleErrors()
    {
        $customer = Customer::createNoValidate();

        $firstToken = 'FIRST_PAYPALToken-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $firstNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $firstToken
            )
        ));
        $firstPaypalAccount = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $firstNonce
        ));
        $this->assertTrue($firstPaypalAccount->success);

        $secondToken = 'SECOND_PAYPALToken-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $secondNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $secondToken
            )
        ));
        $secondPaypalAccount = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $secondNonce
        ));
        $this->assertTrue($secondPaypalAccount->success);

        $updateResult = PayPalAccount::update($firstToken, array(
            'token' => $secondToken
        ));

        $this->assertFalse($updateResult->success);
        $errors = $updateResult->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Error_Codes::PAYPAL_ACCOUNT_TOKEN_IS_IN_USE, $errors[0]->code);
    }

    function testDelete()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        PayPalAccount::delete($paymentMethodToken);

        $this->setExpectedException('Exception_NotFound');
        PayPalAccount::find($paymentMethodToken);
    }

    function testSale_createsASaleUsingGivenToken()
    {
        $nonce = Nonces::$paypalFuturePayment;
        $customer = Customer::createNoValidate(array(
            'paymentMethodNonce' => $nonce
        ));
        $paypalAccount = $customer->paypalAccounts[0];

        $result = PayPalAccount::sale($paypalAccount->token, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($paypalAccount->token, $result->transaction->paypalDetails->token);
    }
}
