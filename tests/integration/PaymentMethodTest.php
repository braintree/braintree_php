<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_PaymentMethodTest extends PHPUnit_Framework_TestCase
{
    function testCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ),
            'share' => true
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->creditCard->bin);
        $this->assertSame('1111', $result->creditCard->last4);
        $this->assertNotNull($result->creditCard->token);
    }

    function testCreate_fromUnvalidatedCreditCardNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->creditCard->bin);
        $this->assertSame('1111', $result->creditCard->last4);
        $this->assertNotNull($result->creditCard->token);
    }

    function testCreate_fromUnvalidatedFuturePaypalAccountNonce()
    {
        altpayMerchantConfig();
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('jane.doe@example.com', $result->paypalAccount->email);
        $this->assertSame($paymentMethodToken, $result->paypalAccount->token);
        integrationMerchantConfig();
    }

    function testCreate_doesNotWorkForUnvalidatedOnetimePaypalAccountNonce()
    {
        altpayMerchantConfig();
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
        integrationMerchantConfig();
    }

    function testCreate_handlesValidationErrorsForPayPalAccounts()
    {
        altpayMerchantConfig();
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CONSENT_CODE_OR_ACCESS_TOKEN_IS_REQUIRED, $errors[1]->code);
        integrationMerchantConfig();
    }

    function testFind_returnsCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken
        ));
        $this->assertTrue($creditCardResult->success);

        $foundCreditCard = Braintree_PaymentMethod::find($creditCardResult->creditCard->token);

        $this->assertEquals($paymentMethodToken, $foundCreditCard->token);
        $this->assertEquals('510510', $foundCreditCard->bin);
        $this->assertEquals('5100', $foundCreditCard->last4);
        $this->assertEquals('05/2011', $foundCreditCard->expirationDate);
    }

    function testFind_returnsCreditCardsWithSubscriptions()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
        ));
        $this->assertTrue($creditCardResult->success);

        $subscriptionId = strval(rand());
        Braintree_Subscription::create(array(
            'id' => $subscriptionId,
            'paymentMethodToken' => $creditCardResult->creditCard->token,
            'planId' => 'integration_trialless_plan',
            'price' => '1.00'
        ));

        $foundCreditCard = Braintree_PaymentMethod::find($creditCardResult->creditCard->token);
        $this->assertEquals($subscriptionId, $foundCreditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $foundCreditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $foundCreditCard->subscriptions[0]->price);
    }

    function testFind_returnsPayPalAccounts()
    {
        altpayMerchantConfig();
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
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

        $foundPayPalAccount = Braintree_PaymentMethod::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
        integrationMerchantConfig();
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find('NON_EXISTENT_TOKEN');
    }

    function testDelete_worksWithCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken
        ));
        $this->assertTrue($creditCardResult->success);

        Braintree_PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
        integrationMerchantConfig();
    }

    function testDelete_worksWithPayPalAccounts()
    {
        altpayMerchantConfig();
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_HttpClientApi::nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $paypalAccountResult = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($paypalAccountResult->success);

        Braintree_PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
        integrationMerchantConfig();
    }

}
