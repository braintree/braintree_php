<?php namespace Braintree\Tests\Integration;

use Braintree\Address;
use Braintree\ApplePayCard;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Subscription;
use Braintree\Tests\TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class PaymentMethodTest extends \PHPUnit_Framework_TestCase
{
    function testCreate_fromVaultedCreditCardNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear'  => '2099'
            ),
            'share'       => true
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
    }

    function testGatewayCreate_fromVaultedCreditCardNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear'  => '2099'
            ),
            'share'       => true
        ));

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $result = $gateway->paymentMethod()->create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
    }

    function testCreate_fromFakeApplePayNonce()
    {
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => Test_Nonces::$applePayVisa
        ));

        $this->assertTrue($result->success);
        $applePayCard = $result->paymentMethod;
        $this->assertNotNull($applePayCard->token);
        $this->assertSame(ApplePayCard::VISA, $applePayCard->cardType);
        $this->assertContains("Visa ", $applePayCard->paymentInstrumentName);
        $this->assertTrue($applePayCard->default);
        $this->assertContains('apple_pay', $applePayCard->imageUrl);
        $this->assertTrue(intval($applePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($applePayCard->expirationYear) > 0);
    }

    function testCreate_fromFakeAndroidPayNonce()
    {
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => Test_Nonces::$androidPay
        ));

        $this->assertTrue($result->success);
        $androidPayCard = $result->paymentMethod;
        $this->assertNotNull($androidPayCard->token);
        $this->assertSame(CreditCard::DISCOVER, $androidPayCard->virtualCardType);
        $this->assertSame(CreditCard::DISCOVER, $androidPayCard->cardType);
        $this->assertSame("1117", $androidPayCard->virtualCardLast4);
        $this->assertSame("1117", $androidPayCard->last4);
        $this->assertSame(CreditCard::VISA, $androidPayCard->sourceCardType);
        $this->assertSame("1111", $androidPayCard->sourceCardLast4);
        $this->assertTrue($androidPayCard->default);
        $this->assertContains('android_pay', $androidPayCard->imageUrl);
        $this->assertTrue(intval($androidPayCard->expirationMonth) > 0);
        $this->assertTrue(intval($androidPayCard->expirationYear) > 0);
    }

    function testCreate_fromUnvalidatedCreditCardNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear'  => '2099',
                'options'         => array(
                    'validate' => false
                )
            )
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
    }

    function testCreate_fromUnvalidatedFuturePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('jane.doe@example.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
    }

    function testCreate_fromAbstractPaymentMethodNonce()
    {
        $customer = Customer::createNoValidate();

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => Test_Nonces::$abstractTransactable
        ));

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethod->token);
    }

    function testCreate_doesNotWorkForUnvalidatedOnetimePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token'        => $paymentMethodToken
            )
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
    }

    function testCreate_handlesValidationErrorsForPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'token' => $paymentMethodToken
            )
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
        $this->assertEquals(Error_Codes::PAYPAL_ACCOUNT_CONSENT_CODE_OR_ACCESS_TOKEN_IS_REQUIRED, $errors[1]->code);
    }

    function testCreate_allowsPassingDefaultOptionWithNonce()
    {
        $customer = Customer::createNoValidate();
        $card1 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $this->assertTrue($card1->isDefault());

        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear'  => '2099',
                'options'         => array(
                    'validate' => false
                )
            )
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'makeDefault' => true
            )
        ));

        $card2 = $result->paymentMethod;
        $card1 = CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    function testCreate_overridesNonceToken()
    {
        $customer = Customer::createNoValidate();
        $firstToken = 'FIRST_TOKEN-' . strval(rand());
        $secondToken = 'SECOND_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'token'           => $firstToken,
                'number'          => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear'  => '2099',
                'options'         => array(
                    'validate' => false
                )
            )
        ));

        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token'              => $secondToken
        ));

        $card = $result->paymentMethod;
        $this->assertEquals($secondToken, $card->token);
    }

    function testCreate_respectsVerifyCardAndVerificationMerchantAccountIdWhenIncludedOutsideOfTheNonce()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4000111111111115',
                'expirationMonth' => '11',
                'expirationYear'  => '2099',
            )
        ));
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'options'            => array(
                'verifyCard'                    => 'true',
                'verificationMerchantAccountId' => TestHelper::nonDefaultMerchantAccountId()
            )
        ));

        $this->assertFalse($result->success);
        $this->assertEquals(Result_CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals(TestHelper::nonDefaultMerchantAccountId(),
            $result->creditCardVerification->merchantAccountId);
    }

    function testCreate_respectsFailOnDuplicatePaymentMethodWhenIncludedOutsideTheNonce()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($result->success);

        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'         => Test_CreditCardNumbers::$visa,
                'expirationDate' => "05/2012"
            )
        ));
        $updateResult = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'options'            => array(
                'failOnDuplicatePaymentMethod' => 'true',
            )
        ));

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals("81724", $resultErrors[0]->code);
    }

    function testCreate_allowsPassingABillingAddressOutsideOfTheNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear'  => '2020',
                'options'         => array(
                    'validate' => false
                )
            )
        ));

        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'billingAddress'     => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('123 Abc Way', $foundCreditCard->billingAddress->streetAddress);
    }

    function testCreate_overridesTheBillingAddressInTheNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear'  => '2020',
                'options'         => array(
                    'validate' => false
                ),
                'billingAddress'  => array(
                    'streetAddress' => '456 Xyz Way'
                )
            )
        ));

        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'billingAddress'     => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('123 Abc Way', $foundCreditCard->billingAddress->streetAddress);
    }

    function testCreate_doesNotOverrideTheBillingAddressForAVaultedCreditCard()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'customerId'  => $customer->id,
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear'  => '2020',
                'billingAddress'  => array(
                    'streetAddress' => '456 Xyz Way'
                )
            )
        ));

        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'billingAddress'     => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('456 Xyz Way', $foundCreditCard->billingAddress->streetAddress);
    }

    function testCreate_allowsPassingABillingAddressIdOutsideOfTheNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number'          => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear'  => '2020',
                'options'         => array(
                    'validate' => false
                )
            )
        ));

        $address = Address::create(array(
            'customerId' => $customer->id,
            'firstName'  => 'Bobby',
            'lastName'   => 'Tables'
        ))->address;
        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'billingAddressId'   => $address->id
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('Bobby', $foundCreditCard->billingAddress->firstName);
        $this->assertEquals('Tables', $foundCreditCard->billingAddress->lastName);
    }

    function testCreate_doesNotReturnAnErrorIfCreditCardOptionsArePresentForAPaypalNonce()
    {
        $customer = Customer::createNoValidate();
        $originalToken = 'paypal-account-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'consent-code',
                'token'       => $originalToken
            )
        ));

        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'options'            => array(
                'verifyCard'                    => 'true',
                'failOnDuplicatePaymentMethod'  => 'true',
                'verificationMerchantAccountId' => 'Not a Real Merchant Account Id'
            )
        ));

        $this->assertTrue($result->success);
    }

    function testCreate_ignoresPassedBillingAddressParamsForPaypalAccount()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            )
        ));
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'billingAddress'     => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = PaypalAccount::find($token);
        $this->assertTrue(null != $foundPaypalAccount);
    }

    function testCreate_ignoresPassedBillingAddressIdForPaypalAccount()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            )
        ));
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id,
            'billingAddressId'   => 'address_id'
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = PaypalAccount::find($token);
        $this->assertTrue(null != $foundPaypalAccount);
    }

    function testCreate_acceptsNumberAndOtherCreditCardParameters()
    {
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => Test_Nonces::$transactable,
            'cardholderName'     => 'Jane Doe',
            'cvv'                => '123',
            'expirationMonth'    => '10',
            'expirationYear'     => '24',
            'number'             => '4242424242424242'
        ));

        $this->assertTrue($result->success);
        $this->assertTrue('Jane Doe' == $result->paymentMethod->cardholderName);
        $this->assertTrue('10' == $result->paymentMethod->expirationMonth);
        $this->assertTrue('2024' == $result->paymentMethod->expirationYear);
        $this->assertTrue('424242' == $result->paymentMethod->bin);
        $this->assertTrue('4242' == $result->paymentMethod->last4);
    }

    function testFind_returnsCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'token'          => $paymentMethodToken
        ));
        $this->assertTrue($creditCardResult->success);

        $foundCreditCard = PaymentMethod::find($creditCardResult->creditCard->token);

        $this->assertEquals($paymentMethodToken, $foundCreditCard->token);
        $this->assertEquals('510510', $foundCreditCard->bin);
        $this->assertEquals('5100', $foundCreditCard->last4);
        $this->assertEquals('05/2011', $foundCreditCard->expirationDate);
    }

    function testFind_returnsCreditCardsWithSubscriptions()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
        ));
        $this->assertTrue($creditCardResult->success);

        $subscriptionId = strval(rand());
        Subscription::create(array(
            'id'                 => $subscriptionId,
            'paymentMethodToken' => $creditCardResult->creditCard->token,
            'planId'             => 'integration_trialless_plan',
            'price'              => '1.00'
        ));

        $foundCreditCard = PaymentMethod::find($creditCardResult->creditCard->token);
        $this->assertEquals($subscriptionId, $foundCreditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $foundCreditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $foundCreditCard->subscriptions[0]->price);
    }

    function testFind_returnsPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
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

        $foundPayPalAccount = PaymentMethod::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
    }

    function testFind_returnsApplePayCards()
    {
        $paymentMethodToken = 'APPLE_PAY-' . strval(rand());
        $customer = Customer::createNoValidate();
        $nonce = Test_Nonces::$applePayVisa;
        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token'              => $paymentMethodToken
        ));

        $foundApplePayCard = PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundApplePayCard->token);
        $this->assertInstanceOf('ApplePayCard', $foundApplePayCard);
        $this->assertTrue(intval($foundApplePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundApplePayCard->expirationYear) > 0);
    }

    function testFind_returnsAndroidPayCards()
    {
        $paymentMethodToken = 'ANDROID-PAY-' . strval(rand());
        $customer = Customer::createNoValidate();
        $nonce = Test_Nonces::$androidPay;
        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token'              => $paymentMethodToken
        ));

        $foundAndroidPayCard = PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundAndroidPayCard->token);
        $this->assertInstanceOf('AndroidPayCard', $foundAndroidPayCard);
        $this->assertSame(CreditCard::DISCOVER, $foundAndroidPayCard->virtualCardType);
        $this->assertSame("1117", $foundAndroidPayCard->virtualCardLast4);
        $this->assertSame(CreditCard::VISA, $foundAndroidPayCard->sourceCardType);
        $this->assertSame("1111", $foundAndroidPayCard->sourceCardLast4);
        $this->assertTrue($foundAndroidPayCard->default);
        $this->assertContains('android_pay', $foundAndroidPayCard->imageUrl);
        $this->assertTrue(intval($foundAndroidPayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundAndroidPayCard->expirationYear) > 0);
    }

    function testFind_returnsCoinbaseAccounts()
    {
        $customer = Customer::createNoValidate();
        $result = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => Test_Nonces::$coinbase
        ));

        $this->assertTrue($result->success);
        $coinbaseAccount = $result->paymentMethod;
        $this->assertNotNull($coinbaseAccount->token);
        $foundCoinbaseAccount = PaymentMethod::find($coinbaseAccount->token);
        $this->assertInstanceOf('CoinbaseAccount', $foundCoinbaseAccount);
        $this->assertNotNull($foundCoinbaseAccount->userId);
        $this->assertNotNull($foundCoinbaseAccount->userName);
        $this->assertNotNull($foundCoinbaseAccount->userEmail);
    }


    function testFind_returnsAbstractPaymentMethods()
    {
        $paymentMethodToken = 'ABSTRACT-' . strval(rand());
        $customer = Customer::createNoValidate();
        $nonce = test_Nonces::$abstractTransactable;
        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token'              => $paymentMethodToken
        ));

        $foundPaymentMethod = PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundPaymentMethod->token);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        PaymentMethod::find('NON_EXISTENT_TOKEN');
    }

    function testUpdate_updatesTheCreditCard()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId'     => $customer->id,
            'cvv'            => '123',
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'cvv'            => '456',
            'number'         => Test_CreditCardNumbers::$masterCard,
            'expirationDate' => "06/2013"
        ));

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("New Holder", $updatedCreditCard->cardholderName);
        $this->assertSame(substr(Test_CreditCardNumbers::$masterCard, 0, 6), $updatedCreditCard->bin);
        $this->assertSame(substr(Test_CreditCardNumbers::$masterCard, -4), $updatedCreditCard->last4);
        $this->assertSame("06/2013", $updatedCreditCard->expirationDate);
    }

    function testUpdate_createsANewBillingAddressByDefault()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            )
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("IL", $updatedCreditCard->billingAddress->region);
        $this->assertSame(null, $updatedCreditCard->billingAddress->streetAddress);
        $this->assertFalse($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    function testUpdate_updatesTheBillingAddressIfOptionIsSpecified()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'region'  => 'IL',
                'options' => array(
                    'updateExisting' => 'true'
                )
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("IL", $updatedCreditCard->billingAddress->region);
        $this->assertSame("123 Nigeria Ave", $updatedCreditCard->billingAddress->streetAddress);
        $this->assertTrue($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    function testUpdate_updatesTheCountryViaCodes()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'countryName'        => 'American Samoa',
                'countryCodeAlpha2'  => 'AS',
                'countryCodeAlpha3'  => 'ASM',
                'countryCodeNumeric' => '016',
                'options'            => array(
                    'updateExisting' => 'true'
                )
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("American Samoa", $updatedCreditCard->billingAddress->countryName);
        $this->assertSame("AS", $updatedCreditCard->billingAddress->countryCodeAlpha2);
        $this->assertSame("ASM", $updatedCreditCard->billingAddress->countryCodeAlpha3);
        $this->assertSame("016", $updatedCreditCard->billingAddress->countryCodeNumeric);
    }

    function testUpdate_canPassExpirationMonthAndExpirationYear()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'number'          => Test_CreditCardNumbers::$masterCard,
            'expirationMonth' => "07",
            'expirationYear'  => "2011"
        ));

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("07", $updatedCreditCard->expirationMonth);
        $this->assertSame("07", $updatedCreditCard->expirationMonth);
        $this->assertSame("07/2011", $updatedCreditCard->expirationDate);
    }

    function testUpdate_verifiesTheUpdateIfOptionsVerifyCardIsTrue()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId'     => $customer->id,
            'cvv'            => '123',
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'cvv'            => '456',
            'number'         => Test_CreditCardNumbers::$failsSandboxVerification['MasterCard'],
            'expirationDate' => "06/2013",
            'options'        => array(
                'verifyCard' => 'true'
            )
        ));

        $this->assertFalse($updateResult->success);
        $this->assertEquals(Result_CreditCardVerification::PROCESSOR_DECLINED,
            $updateResult->creditCardVerification->status);
        $this->assertEquals(null, $updateResult->creditCardVerification->gatewayRejectionReason);
    }

    function testUpdate_canUpdateTheBillingAddress()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId'     => $customer->id,
            'cvv'            => '123',
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => array(
                'firstName'       => 'Old First Name',
                'lastName'        => 'Old Last Name',
                'company'         => 'Old Company',
                'streetAddress'   => '123 Old St',
                'extendedAddress' => 'Apt Old',
                'locality'        => 'Old City',
                'region'          => 'Old State',
                'postalCode'      => '12345',
                'countryName'     => 'Canada'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'firstName'       => 'New First Name',
                'lastName'        => 'New Last Name',
                'company'         => 'New Company',
                'streetAddress'   => '123 New St',
                'extendedAddress' => 'Apt New',
                'locality'        => 'New City',
                'region'          => 'New State',
                'postalCode'      => '56789',
                'countryName'     => 'United States of America'
            )
        ));

        $this->assertTrue($updateResult->success);
        $address = $updateResult->paymentMethod->billingAddress;
        $this->assertSame('New First Name', $address->firstName);
        $this->assertSame('New Last Name', $address->lastName);
        $this->assertSame('New Company', $address->company);
        $this->assertSame('123 New St', $address->streetAddress);
        $this->assertSame('Apt New', $address->extendedAddress);
        $this->assertSame('New City', $address->locality);
        $this->assertSame('New State', $address->region);
        $this->assertSame('56789', $address->postalCode);
        $this->assertSame('United States of America', $address->countryName);
    }

    function testUpdate_returnsAnErrorIfInvalid()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'number'         => 'invalid',
            'expirationDate' => "05/2014",
        ));

        $this->assertFalse($updateResult->success);
        $numberErrors = $updateResult->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals("Credit card number must be 12-19 digits.", $numberErrors[0]->message);
    }

    function testUpdate_canUpdateTheDefault()
    {
        $customer = Customer::createNoValidate();

        $creditCardResult1 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2009"
        ));
        $this->assertTrue($creditCardResult1->success);
        $creditCard1 = $creditCardResult1->creditCard;

        $creditCardResult2 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2009"
        ));
        $this->assertTrue($creditCardResult2->success);
        $creditCard2 = $creditCardResult2->creditCard;

        $this->assertTrue($creditCard1->default);
        $this->assertFalse($creditCard2->default);


        $updateResult = PaymentMethod::update($creditCard2->token, array(
            'options' => array(
                'makeDefault' => 'true'
            )
        ));
        $this->assertTrue($updateResult->success);

        $this->assertFalse(PaymentMethod::find($creditCard1->token)->default);
        $this->assertTrue(PaymentMethod::find($creditCard2->token)->default);
    }

    function testUpdate_updatesAPaypalAccountsToken()
    {
        $customer = Customer::createNoValidate();
        $originalToken = 'paypal-account-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token'        => $originalToken
            )
        ));

        $originalResult = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id
        ));
        $this->assertTrue($originalResult->success);

        $originalPaypalAccount = $originalResult->paymentMethod;

        $updatedToken = 'UPDATED_TOKEN-' . strval(rand());
        $updateResult = PaymentMethod::update($originalPaypalAccount->token, array(
            'token' => $updatedToken
        ));
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = PaymentMethod::find($updatedToken);
        $this->assertEquals($originalPaypalAccount->email, $updatedPaypalAccount->email);

        $this->setExpectedException('Braintree\Exception\NotFound', 'payment method with token ' . $originalToken . ' not found');
        PaymentMethod::find($originalToken);

    }

    function testUpdate_canMakeAPaypalAccountTheDefaultPaymentMethod()
    {
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2009",
            'options'        => array(
                'makeDefault' => 'true'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
            )
        ));

        $originalToken = PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId'         => $customer->id
        ))->paymentMethod->token;

        $updateResult = PaymentMethod::update($originalToken, array(
            'options' => array(
                'makeDefault' => 'true'
            )
        ));
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = PaymentMethod::find($originalToken);
        $this->assertTrue($updatedPaypalAccount->default);

    }

    function testUpdate_returnsAnErrorIfATokenForAccountIsUsedToAttemptAnUpdate()
    {
        $customer = Customer::createNoValidate();
        $firstToken = 'paypal-account-' . strval(rand());
        $secondToken = 'paypal-account-' . strval(rand());

        $http = new HttpClientApi(Configuration::$global);
        $firstNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token'        => $firstToken
            )
        ));
        $firstResult = PaymentMethod::create(array(
            'paymentMethodNonce' => $firstNonce,
            'customerId'         => $customer->id
        ));
        $this->assertTrue($firstResult->success);
        $firstPaypalAccount = $firstResult->paymentMethod;

        $http = new HttpClientApi(Configuration::$global);
        $secondNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token'        => $secondToken
            )
        ));
        $secondResult = PaymentMethod::create(array(
            'paymentMethodNonce' => $secondNonce,
            'customerId'         => $customer->id
        ));
        $this->assertTrue($secondResult->success);
        $secondPaypalAccount = $firstResult->paymentMethod;


        $updateResult = PaymentMethod::update($firstToken, array(
            'token' => $secondToken
        ));

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals("92906", $resultErrors[0]->code);

    }

    function testDelete_worksWithCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $creditCardResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'token'          => $paymentMethodToken
        ));
        $this->assertTrue($creditCardResult->success);

        PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree\Exception\NotFound');
        PaymentMethod::find($paymentMethodToken);
        TestHelper::integrationMerchantConfig();
    }

    function testDelete_worksWithPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $paypalAccountResult = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($paypalAccountResult->success);

        PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree\Exception\NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

}
