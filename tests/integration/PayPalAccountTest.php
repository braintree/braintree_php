<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_PayPalAccountTest extends PHPUnit_Framework_TestCase
{
    function testFind()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $foundPaypalAccount = Braintree_PayPalAccount::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPaypalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPaypalAccount->token);
    }

    function testFind_doesNotReturnIncorrectPaymentMethodType()
    {
        $creditCardToken = 'creditCardToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'token' => $creditCardToken
        ));
        $this->assertTrue($result->success);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find($creditCardToken);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find('invalid-token');
    }

    function testFind_throwsUsefulErrorMessagesWhenEmpty()
    {
        $this->setExpectedException('InvalidArgumentException', 'expected paypal account id to be set');
        Braintree_PayPalAccount::find('');
    }

    function testFind_throwsUsefulErrorMessagesWhenInvalid()
    {
        $this->setExpectedException('InvalidArgumentException', '@ is an invalid paypal account token');
        Braintree_PayPalAccount::find('@');
    }

    function testUpdate()
    {
        $originalToken = 'ORIGINAL_PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $originalToken
            )
        ));

        $createResult = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($createResult->success);

        $newToken = 'NEW_PAYPALToken-' . strval(rand());
        $updateResult = Braintree_PayPalAccount::update($originalToken, array(
            'token' => $newToken
        ));

        $this->assertTrue($updateResult->success);
        $this->assertEquals($newToken, $updateResult->paypalAccount->token);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find($originalToken);

    }

    function testUpdate_handleErrors()
    {
        $customer = Braintree_Customer::createNoValidate();

        $firstToken = 'FIRST_PAYPALToken-' . strval(rand());
        $firstNonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $firstToken
            )
        ));
        $firstPaypalAccount = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $firstNonce
        ));
        $this->assertTrue($firstPaypalAccount->success);

        $secondToken = 'SECOND_PAYPALToken-' . strval(rand());
        $secondNonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $secondToken
            )
        ));
        $secondPaypalAccount = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $secondNonce
        ));
        $this->assertTrue($secondPaypalAccount->success);

        $updateResult = Braintree_PayPalAccount::update($firstToken, array(
            'token' => $secondToken
        ));

        $this->assertFalse($updateResult->success);
        $errors = $updateResult->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_TOKEN_IS_IN_USE, $errors[0]->code);
    }

    function testDelete()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        Braintree_PayPalAccount::delete($paymentMethodToken);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find($paymentMethodToken);
    }
}
