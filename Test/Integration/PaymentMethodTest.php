<?php
namespace Test\Integration;

require_once dirname(__DIR__).'/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class PaymentMethodTest extends Setup
{
    public function testCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ),
            'share' => true,
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
    }

    public function testGatewayCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ),
            'share' => true,
        ));

        $gateway = new Braintree\Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key',
        ));
        $result = $gateway->paymentMethod()->create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
    }

    public function testCreate_fromFakeApplePayNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$applePayVisa,
        ));

        $this->assertTrue($result->success);
        $applePayCard = $result->paymentMethod;
        $this->assertNotNull($applePayCard->token);
        $this->assertSame(Braintree\ApplePayCard::VISA, $applePayCard->cardType);
        $this->assertContains('Visa ', $applePayCard->paymentInstrumentName);
        $this->assertTrue($applePayCard->default);
        $this->assertContains('apple_pay', $applePayCard->imageUrl);
        $this->assertTrue(intval($applePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($applePayCard->expirationYear) > 0);
    }

    public function testCreate_fromFakeAndroidPayProxyCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$androidPayDiscover
        ));
        $this->assertTrue($result->success);
        $androidPayCard = $result->paymentMethod;
        $this->assertNotNull($androidPayCard->token);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $androidPayCard->virtualCardType);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $androidPayCard->cardType);
        $this->assertSame('1117', $androidPayCard->virtualCardLast4);
        $this->assertSame('1117', $androidPayCard->last4);
        $this->assertSame(Braintree\CreditCard::VISA, $androidPayCard->sourceCardType);
        $this->assertSame('1111', $androidPayCard->sourceCardLast4);
        $this->assertSame('Visa 1111', $androidPayCard->sourceDescription);
        $this->assertTrue($androidPayCard->default);
        $this->assertContains('android_pay', $androidPayCard->imageUrl);
        $this->assertTrue(intval($androidPayCard->expirationMonth) > 0);
        $this->assertTrue(intval($androidPayCard->expirationYear) > 0);
    }

    public function testCreate_fromFakeAndroidPayNetworkTokenNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$androidPayMasterCard
        ));

        $this->assertTrue($result->success);
        $androidPayCard = $result->paymentMethod;
        $this->assertNotNull($androidPayCard->token);
        $this->assertSame(Braintree\CreditCard::MASTER_CARD, $androidPayCard->virtualCardType);
        $this->assertSame(Braintree\CreditCard::MASTER_CARD, $androidPayCard->cardType);
        $this->assertSame('4444', $androidPayCard->virtualCardLast4);
        $this->assertSame('4444', $androidPayCard->last4);
        $this->assertSame(Braintree\CreditCard::MASTER_CARD, $androidPayCard->sourceCardType);
        $this->assertSame('4444', $androidPayCard->sourceCardLast4);
        $this->assertSame('MasterCard 4444', $androidPayCard->sourceDescription);
        $this->assertTrue($androidPayCard->default);
        $this->assertContains('android_pay', $androidPayCard->imageUrl);
        $this->assertTrue(intval($androidPayCard->expirationMonth) > 0);
        $this->assertTrue(intval($androidPayCard->expirationYear) > 0);
    }


    public function testCreate_fromUnvalidatedCreditCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false,
                ),
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
    }

    public function testCreate_fromUnvalidatedFuturePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken,
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertSame('jane.doe@example.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
    }

    public function testCreate_fromAbstractPaymentMethodNonce()
    {
        $customer = Braintree\Customer::createNoValidate();

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$abstractTransactable,
        ));

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethod->token);
    }

    public function testCreate_doesNotWorkForUnvalidatedOnetimePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paymentMethodToken,
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree\Error\Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
    }

    public function testCreate_handlesValidationErrorsForPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'token' => $paymentMethodToken,
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree\Error\Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
        $this->assertEquals(Braintree\Error\Codes::PAYPAL_ACCOUNT_CONSENT_CODE_OR_ACCESS_TOKEN_IS_REQUIRED, $errors[1]->code);
    }

    public function testCreate_allowsPassingDefaultOptionWithNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card1 = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        ))->creditCard;

        $this->assertTrue($card1->isDefault());

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false,
                ),
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'makeDefault' => true,
            ),
        ));

        $card2 = $result->paymentMethod;
        $card1 = Braintree\CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    public function testCreate_overridesNonceToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $firstToken = 'FIRST_TOKEN-'.strval(rand());
        $secondToken = 'SECOND_TOKEN-'.strval(rand());
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'token' => $firstToken,
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false,
                ),
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $secondToken,
        ));

        $card = $result->paymentMethod;
        $this->assertEquals($secondToken, $card->token);
    }

    public function testCreate_respectsVerifyCardAndVerificationMerchantAccountIdWhenIncludedOutsideOfTheNonce()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4000111111111115',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ),
        ));
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => array(
                'verifyCard' => 'true',
                'verificationMerchantAccountId' => Test\Helper::nonDefaultMerchantAccountId(),
            ),
        ));

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals(Test\Helper::nonDefaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    public function testCreate_respectsFailOnDuplicatePaymentMethodWhenIncludedOutsideTheNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
        ));
        $this->assertTrue($result->success);

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
            ),
        ));
        $updateResult = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => array(
                'failOnDuplicatePaymentMethod' => 'true',
            ),
        ));

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals('81724', $resultErrors[0]->code);
    }

    public function testCreate_allowsPassingABillingAddressOutsideOfTheNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => array(
                    'validate' => false,
                ),
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way',
            ),
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree\CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('123 Abc Way', $foundCreditCard->billingAddress->streetAddress);
    }

    public function testCreate_overridesTheBillingAddressInTheNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => array(
                    'validate' => false,
                ),
                'billingAddress' => array(
                    'streetAddress' => '456 Xyz Way',
                ),
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way',
            ),
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree\CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('123 Abc Way', $foundCreditCard->billingAddress->streetAddress);
    }

    public function testCreate_doesNotOverrideTheBillingAddressForAVaultedCreditCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'customerId' => $customer->id,
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'billingAddress' => array(
                    'streetAddress' => '456 Xyz Way',
                ),
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way',
            ),
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree\CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('456 Xyz Way', $foundCreditCard->billingAddress->streetAddress);
    }

    public function testCreate_allowsPassingABillingAddressIdOutsideOfTheNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => array(
                    'validate' => false,
                ),
            ),
        ));

        $address = Braintree\Address::create(array(
            'customerId' => $customer->id,
            'firstName' => 'Bobby',
            'lastName' => 'Tables',
        ))->address;
        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddressId' => $address->id,
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree\CreditCard::find($token);
        $this->assertTrue(null != $foundCreditCard);
        $this->assertEquals('Bobby', $foundCreditCard->billingAddress->firstName);
        $this->assertEquals('Tables', $foundCreditCard->billingAddress->lastName);
    }

    public function testCreate_doesNotReturnAnErrorIfCreditCardOptionsArePresentForAPaypalNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $originalToken = 'paypal-account-'.strval(rand());
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'consent-code',
                'token' => $originalToken,
            ),
        ));

        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => array(
                'verifyCard' => 'true',
                'failOnDuplicatePaymentMethod' => 'true',
                'verificationMerchantAccountId' => 'Not a Real Merchant Account Id',
            ),
        ));

        $this->assertTrue($result->success);
    }

    public function testCreate_ignoresPassedBillingAddressParamsForPaypalAccount()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            ),
        ));
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way',
            ),
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = Braintree\PaypalAccount::find($token);
        $this->assertTrue(null != $foundPaypalAccount);
    }

    public function testCreate_ignoresPassedBillingAddressIdForPaypalAccount()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            ),
        ));
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddressId' => 'address_id',
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = Braintree\PaypalAccount::find($token);
        $this->assertTrue(null != $foundPaypalAccount);
    }

    public function testCreate_acceptsNumberAndOtherCreditCardParameters()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$transactable,
            'cardholderName' => 'Jane Doe',
            'cvv' => '123',
            'expirationMonth' => '10',
            'expirationYear' => '24',
            'number' => '4242424242424242',
        ));
        $this->assertTrue($result->success);
        $this->assertTrue('Jane Doe' == $result->paymentMethod->cardholderName);
        $this->assertTrue('10' == $result->paymentMethod->expirationMonth);
        $this->assertTrue('2024' == $result->paymentMethod->expirationYear);
        $this->assertTrue('424242' == $result->paymentMethod->bin);
        $this->assertTrue('4242' == $result->paymentMethod->last4);
    }

    public function testFind_returnsCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken,
        ));
        $this->assertTrue($creditCardResult->success);

        $foundCreditCard = Braintree\PaymentMethod::find($creditCardResult->creditCard->token);

        $this->assertEquals($paymentMethodToken, $foundCreditCard->token);
        $this->assertEquals('510510', $foundCreditCard->bin);
        $this->assertEquals('5100', $foundCreditCard->last4);
        $this->assertEquals('05/2011', $foundCreditCard->expirationDate);
    }

    public function testFind_returnsCreditCardsWithSubscriptions()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
        ));
        $this->assertTrue($creditCardResult->success);

        $subscriptionId = strval(rand());
        Braintree\Subscription::create(array(
            'id' => $subscriptionId,
            'paymentMethodToken' => $creditCardResult->creditCard->token,
            'planId' => 'integration_trialless_plan',
            'price' => '1.00',
        ));

        $foundCreditCard = Braintree\PaymentMethod::find($creditCardResult->creditCard->token);
        $this->assertEquals($subscriptionId, $foundCreditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $foundCreditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $foundCreditCard->subscriptions[0]->price);
    }

    public function testFind_returnsPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken,
            ),
        ));

        Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));

        $foundPayPalAccount = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
    }

    public function testFind_returnsApplePayCards()
    {
        $paymentMethodToken = 'APPLE_PAY-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $nonce = Braintree\Test\Nonces::$applePayVisa;
        Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken,
        ));

        $foundApplePayCard = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundApplePayCard->token);
        $this->assertInstanceOf('Braintree\ApplePayCard', $foundApplePayCard);
        $this->assertTrue(intval($foundApplePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundApplePayCard->expirationYear) > 0);
    }

    public function testFind_returnsAndroidPayCards()
    {
        $paymentMethodToken = 'ANDROID-PAY-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $nonce = Braintree\Test\Nonces::$androidPay;
        Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken
        ));
        $foundAndroidPayCard = Braintree\PaymentMethod::find($paymentMethodToken);
        $this->assertSame($paymentMethodToken, $foundAndroidPayCard->token);
        $this->assertInstanceOf('Braintree\AndroidPayCard', $foundAndroidPayCard);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $foundAndroidPayCard->virtualCardType);
        $this->assertSame('1117', $foundAndroidPayCard->virtualCardLast4);
        $this->assertSame(Braintree\CreditCard::VISA, $foundAndroidPayCard->sourceCardType);
        $this->assertSame('1111', $foundAndroidPayCard->sourceCardLast4);
        $this->assertTrue($foundAndroidPayCard->default);
        $this->assertContains('android_pay', $foundAndroidPayCard->imageUrl);
        $this->assertTrue(intval($foundAndroidPayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundAndroidPayCard->expirationYear) > 0);
    }

    public function testFind_returnsCoinbaseAccounts()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$coinbase,
        ));

        $this->assertTrue($result->success);
        $coinbaseAccount = $result->paymentMethod;
        $this->assertNotNull($coinbaseAccount->token);
        $foundCoinbaseAccount = Braintree\PaymentMethod::find($coinbaseAccount->token);
        $this->assertInstanceOf('Braintree\CoinbaseAccount', $foundCoinbaseAccount);
        $this->assertNotNull($foundCoinbaseAccount->userId);
        $this->assertNotNull($foundCoinbaseAccount->userName);
        $this->assertNotNull($foundCoinbaseAccount->userEmail);
    }

    public function testFind_returnsAbstractPaymentMethods()
    {
        $paymentMethodToken = 'ABSTRACT-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $nonce = Braintree\Test\Nonces::$abstractTransactable;
        Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken,
        ));

        $foundPaymentMethod = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundPaymentMethod->token);
    }

    public function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        Braintree\PaymentMethod::find('NON_EXISTENT_TOKEN');
    }

    public function testUpdate_updatesTheCreditCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree\Test\CreditCardNumbers::$masterCard,
            'expirationDate' => '06/2013',
        ));

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame('New Holder', $updatedCreditCard->cardholderName);
        $this->assertSame(substr(Braintree\Test\CreditCardNumbers::$masterCard, 0, 6), $updatedCreditCard->bin);
        $this->assertSame(substr(Braintree\Test\CreditCardNumbers::$masterCard, -4), $updatedCreditCard->last4);
        $this->assertSame('06/2013', $updatedCreditCard->expirationDate);
    }

    public function testUpdate_createsANewBillingAddressByDefault()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave',
            ),
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL',
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame('IL', $updatedCreditCard->billingAddress->region);
        $this->assertSame(null, $updatedCreditCard->billingAddress->streetAddress);
        $this->assertFalse($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    public function testUpdate_updatesTheBillingAddressIfOptionIsSpecified()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave',
            ),
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL',
                'options' => array(
                    'updateExisting' => 'true',
                ),
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame('IL', $updatedCreditCard->billingAddress->region);
        $this->assertSame('123 Nigeria Ave', $updatedCreditCard->billingAddress->streetAddress);
        $this->assertTrue($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    public function testUpdate_updatesTheCountryViaCodes()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave',
            ),
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'countryName' => 'American Samoa',
                'countryCodeAlpha2' => 'AS',
                'countryCodeAlpha3' => 'ASM',
                'countryCodeNumeric' => '016',
                'options' => array(
                    'updateExisting' => 'true',
                ),
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame('American Samoa', $updatedCreditCard->billingAddress->countryName);
        $this->assertSame('AS', $updatedCreditCard->billingAddress->countryCodeAlpha2);
        $this->assertSame('ASM', $updatedCreditCard->billingAddress->countryCodeAlpha3);
        $this->assertSame('016', $updatedCreditCard->billingAddress->countryCodeNumeric);
    }

    public function testUpdate_canPassExpirationMonthAndExpirationYear()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'number' => Braintree\Test\CreditCardNumbers::$masterCard,
            'expirationMonth' => '07',
            'expirationYear' => '2011',
        ));

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame('07', $updatedCreditCard->expirationMonth);
        $this->assertSame('07', $updatedCreditCard->expirationMonth);
        $this->assertSame('07/2011', $updatedCreditCard->expirationDate);
    }

    public function testUpdate_verifiesTheUpdateIfOptionsVerifyCardIsTrue()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree\Test\CreditCardNumbers::$failsSandboxVerification['MasterCard'],
            'expirationDate' => '06/2013',
            'options' => array(
                'verifyCard' => 'true',
            ),
        ));

        $this->assertFalse($updateResult->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $updateResult->creditCardVerification->status);
        $this->assertEquals(null, $updateResult->creditCardVerification->gatewayRejectionReason);
    }

    public function testUpdate_canUpdateTheBillingAddress()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => array(
                'firstName' => 'Old First Name',
                'lastName' => 'Old Last Name',
                'company' => 'Old Company',
                'streetAddress' => '123 Old St',
                'extendedAddress' => 'Apt Old',
                'locality' => 'Old City',
                'region' => 'Old State',
                'postalCode' => '12345',
                'countryName' => 'Canada',
            ),
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'firstName' => 'New First Name',
                'lastName' => 'New Last Name',
                'company' => 'New Company',
                'streetAddress' => '123 New St',
                'extendedAddress' => 'Apt New',
                'locality' => 'New City',
                'region' => 'New State',
                'postalCode' => '56789',
                'countryName' => 'United States of America',
            ),
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

    public function testUpdate_returnsAnErrorIfInvalid()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'number' => 'invalid',
            'expirationDate' => '05/2014',
        ));

        $this->assertFalse($updateResult->success);
        $numberErrors = $updateResult->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals('Credit card number must be 12-19 digits.', $numberErrors[0]->message);
    }

    public function testUpdate_canUpdateTheDefault()
    {
        $customer = Braintree\Customer::createNoValidate();

        $creditCardResult1 = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2009',
        ));
        $this->assertTrue($creditCardResult1->success);
        $creditCard1 = $creditCardResult1->creditCard;

        $creditCardResult2 = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2009',
        ));
        $this->assertTrue($creditCardResult2->success);
        $creditCard2 = $creditCardResult2->creditCard;

        $this->assertTrue($creditCard1->default);
        $this->assertFalse($creditCard2->default);

        $updateResult = Braintree\PaymentMethod::update($creditCard2->token, array(
            'options' => array(
                'makeDefault' => 'true',
            ),
        ));
        $this->assertTrue($updateResult->success);

        $this->assertFalse(Braintree\PaymentMethod::find($creditCard1->token)->default);
        $this->assertTrue(Braintree\PaymentMethod::find($creditCard2->token)->default);
    }

    public function testUpdate_updatesAPaypalAccountsToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $originalToken = 'paypal-account-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $originalToken,
            ),
        ));

        $originalResult = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
        ));
        $this->assertTrue($originalResult->success);

        $originalPaypalAccount = $originalResult->paymentMethod;

        $updatedToken = 'UPDATED_TOKEN-'.strval(rand());
        $updateResult = Braintree\PaymentMethod::update($originalPaypalAccount->token, array(
            'token' => $updatedToken,
        ));
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = Braintree\PaymentMethod::find($updatedToken);
        $this->assertEquals($originalPaypalAccount->email, $updatedPaypalAccount->email);

        $this->setExpectedException('Braintree\Exception\NotFound', 'payment method with token '.$originalToken.' not found');
        Braintree\PaymentMethod::find($originalToken);
    }

    public function testUpdate_canMakeAPaypalAccountTheDefaultPaymentMethod()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2009',
            'options' => array(
                'makeDefault' => 'true',
            ),
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
            ),
        ));

        $originalToken = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
        ))->paymentMethod->token;

        $updateResult = Braintree\PaymentMethod::update($originalToken, array(
            'options' => array(
                'makeDefault' => 'true',
            ),
        ));
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = Braintree\PaymentMethod::find($originalToken);
        $this->assertTrue($updatedPaypalAccount->default);
    }

    public function testUpdate_returnsAnErrorIfATokenForAccountIsUsedToAttemptAnUpdate()
    {
        $customer = Braintree\Customer::createNoValidate();
        $firstToken = 'paypal-account-'.strval(rand());
        $secondToken = 'paypal-account-'.strval(rand());

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $firstNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $firstToken,
            ),
        ));
        $firstResult = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $firstNonce,
            'customerId' => $customer->id,
        ));
        $this->assertTrue($firstResult->success);
        $firstPaypalAccount = $firstResult->paymentMethod;

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $secondNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $secondToken,
            ),
        ));
        $secondResult = Braintree\PaymentMethod::create(array(
            'paymentMethodNonce' => $secondNonce,
            'customerId' => $customer->id,
        ));
        $this->assertTrue($secondResult->success);
        $secondPaypalAccount = $firstResult->paymentMethod;

        $updateResult = Braintree\PaymentMethod::update($firstToken, array(
            'token' => $secondToken,
        ));

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals('92906', $resultErrors[0]->code);
    }

    public function testDelete_worksWithCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken,
        ));
        $this->assertTrue($creditCardResult->success);

        Braintree\PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree\Exception\NotFound');
        Braintree\PaymentMethod::find($paymentMethodToken);
        self::integrationMerchantConfig();
    }

    public function testDelete_worksWithPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-'.strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken,
            ),
        ));

        $paypalAccountResult = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ));
        $this->assertTrue($paypalAccountResult->success);

        Braintree\PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree\Exception\NotFound');
        Braintree\PaymentMethod::find($paymentMethodToken);
    }
}
