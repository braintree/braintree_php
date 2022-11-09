<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class PaymentMethodTest extends Setup
{
    public function testCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ],
            'share' => true
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
    }

    public function testCreate_fromThreeDSecureNonceWithInvalidPassThruParams()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$transactable;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'threeDSecurePassThru' => [
                'eciFlag' => '02',
                'cavv' => 'some_cavv',
                'xid' => 'some_xid',
                'threeDSecureVersion' => 'xx',
                'authenticationResponse' => 'Y',
                'directoryResponse' => 'Y',
                'cavvAlgorithm' => '2',
                'dsTransactionId' => 'some_ds_transaction_id',
            ],
            'options' => [
                'verifyCard' => 'true',
            ]
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('verification')->onAttribute('threeDSecureVersion');
        $this->assertEquals(Braintree\Error\Codes::VERIFICATION_THREE_D_SECURE_THREE_D_SECURE_VERSION_IS_INVALID, $errors[0]->code);
        $this->assertEquals(1, preg_match('/The version of 3D Secure authentication must be composed only of digits and separated by periods/', $result->message));
    }

    public function testCreate_fromThreeDSecureNonceWithPassThruParams()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$transactable;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'threeDSecurePassThru' => [
                'eciFlag' => '02',
                'cavv' => 'some_cavv',
                'xid' => 'some_xid',
                'threeDSecureVersion' => '1.0.2',
                'authenticationResponse' => 'Y',
                'directoryResponse' => 'Y',
                'cavvAlgorithm' => '2',
                'dsTransactionId' => 'some_ds_transaction_id',
            ],
            'options' => [
                'verifyCard' => 'true',
            ]
        ]);
        $this->assertTrue($result->success);
    }

    public function testCreate_fromThreeDSecureNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$threeDSecureVisaFullAuthenticationNonce;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => 'true',
            ]
        ]);

        $threeDSecureInfo = $result->paymentMethod->verification->threeDSecureInfo;
        $this->assertTrue($threeDSecureInfo->liabilityShiftPossible);
        $this->assertTrue($threeDSecureInfo->liabilityShifted);
        $this->assertEquals("Y", $threeDSecureInfo->enrolled);
        $this->assertEquals("authenticate_successful", $threeDSecureInfo->status);
        $this->assertEquals("xid_value", $threeDSecureInfo->xid);
        $this->assertEquals("cavv_value", $threeDSecureInfo->cavv);
        $this->assertEquals("05", $threeDSecureInfo->eciFlag);
        $this->assertEquals(null, $threeDSecureInfo->dsTransactionId);
        $this->assertEquals("1.0.2", $threeDSecureInfo->threeDSecureVersion);
    }

    public function testGatewayCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ],
            'share' => true
        ]);

        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
        $result = $gateway->paymentMethod()->create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
    }

    public function testCreate_fromFakeApplePayNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$applePayVisa,
        ]);

        $this->assertTrue($result->success);
        $applePayCard = $result->paymentMethod;
        $this->assertNotNull($applePayCard->token);
        $this->assertNotNull($applePayCard->bin);
        $this->assertNotNull($applePayCard->prepaid);
        $this->assertNotNull($applePayCard->healthcare);
        $this->assertNotNull($applePayCard->debit);
        $this->assertNotNull($applePayCard->durbinRegulated);
        $this->assertNotNull($applePayCard->commercial);
        $this->assertNotNull($applePayCard->payroll);
        $this->assertNotNull($applePayCard->issuingBank);
        $this->assertNotNull($applePayCard->countryOfIssuance);
        $this->assertNotNull($applePayCard->productId);

        $this->assertSame(Braintree\ApplePayCard::VISA, $applePayCard->cardType);
        $this->assertStringContainsString("Visa ", $applePayCard->paymentInstrumentName);
        $this->assertStringContainsString("Visa ", $applePayCard->sourceDescription);
        $this->assertTrue($applePayCard->default);
        $this->assertStringContainsString('apple_pay', $applePayCard->imageUrl);
        $this->assertTrue(intval($applePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($applePayCard->expirationYear) > 0);
        $this->assertSame($customer->id, $applePayCard->customerId);
    }

    public function testCreate_fromFakeGooglePayProxyCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$googlePayDiscover
        ]);

        $this->assertTrue($result->success);
        $googlePayCard = $result->paymentMethod;
        $this->assertNotNull($googlePayCard->token);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $googlePayCard->virtualCardType);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $googlePayCard->cardType);
        $this->assertSame("1117", $googlePayCard->virtualCardLast4);
        $this->assertSame("1117", $googlePayCard->last4);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $googlePayCard->sourceCardType);
        $this->assertSame("1111", $googlePayCard->sourceCardLast4);
        $this->assertSame("Discover 1111", $googlePayCard->sourceDescription);
        $this->assertTrue($googlePayCard->default);
        $this->assertStringContainsString('android_pay', $googlePayCard->imageUrl);
        $this->assertTrue(intval($googlePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($googlePayCard->expirationYear) > 0);
        $this->assertSame($customer->id, $googlePayCard->customerId);
        $this->assertFalse($googlePayCard->isNetworkTokenized);
    }

    public function testCreate_fromFakeGooglePayNetworkTokenNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$googlePayMasterCard
        ]);

        $this->assertTrue($result->success);
        $googlePayCard = $result->paymentMethod;
        $this->assertNotNull($googlePayCard->token);
        $this->assertSame(Braintree\CreditCard::MASTER_CARD, $googlePayCard->virtualCardType);
        $this->assertSame(Braintree\CreditCard::MASTER_CARD, $googlePayCard->cardType);
        $this->assertSame("4444", $googlePayCard->virtualCardLast4);
        $this->assertSame("4444", $googlePayCard->last4);
        $this->assertSame(Braintree\CreditCard::MASTER_CARD, $googlePayCard->sourceCardType);
        $this->assertSame("4444", $googlePayCard->sourceCardLast4);
        $this->assertSame("MasterCard 4444", $googlePayCard->sourceDescription);
        $this->assertTrue($googlePayCard->default);
        $this->assertStringContainsString('android_pay', $googlePayCard->imageUrl);
        $this->assertTrue(intval($googlePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($googlePayCard->expirationYear) > 0);
        $this->assertSame($customer->id, $googlePayCard->customerId);
        $this->assertTrue($googlePayCard->isNetworkTokenized);
        $this->assertNotNull($googlePayCard->prepaid);
        $this->assertNotNull($googlePayCard->healthcare);
        $this->assertNotNull($googlePayCard->debit);
        $this->assertNotNull($googlePayCard->durbinRegulated);
        $this->assertNotNull($googlePayCard->commercial);
        $this->assertNotNull($googlePayCard->payroll);
        $this->assertNotNull($googlePayCard->issuingBank);
        $this->assertNotNull($googlePayCard->countryOfIssuance);
        $this->assertNotNull($googlePayCard->productId);
    }

    public function testCreate_fromFakeVenmoAccountNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$venmoAccount
        ));

        $this->assertTrue($result->success);
        $venmoAccount = $result->paymentMethod;
        $this->assertInstanceOf('Braintree\VenmoAccount', $venmoAccount);

        $this->assertNotNull($venmoAccount->token);
        $this->assertNotNull($venmoAccount->sourceDescription);
        $this->assertStringContainsString(".png", $venmoAccount->imageUrl);
        $this->assertTrue($venmoAccount->default);
        $this->assertSame($customer->id, $venmoAccount->customerId);
        $this->assertEquals(array(), $venmoAccount->subscriptions);
        $this->assertSame("venmojoe", $venmoAccount->username);
        $this->assertSame("1234567891234567891", $venmoAccount->venmoUserId);
    }

    public function testCreate_fromFakeSepaDirectDebitAccountNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$sepaDirectDebit
        ));

        $this->assertTrue($result->success);
        $sepaDirectDebitAccount = $result->paymentMethod;

        $this->assertInstanceOf('Braintree\SepaDirectDebitAccount', $sepaDirectDebitAccount);
        $this->assertEquals($customer->id, $sepaDirectDebitAccount->customerId);
        $this->assertNotNull($sepaDirectDebitAccount->customerGlobalId);
        $this->assertNotNull($sepaDirectDebitAccount->globalId);
        $this->assertNotNull($sepaDirectDebitAccount->imageUrl);
        $this->assertNotNull($sepaDirectDebitAccount->token);
        $this->assertEquals('a-fake-mp-customer-id', $sepaDirectDebitAccount->merchantOrPartnerCustomerId);
        $this->assertEquals(true, $sepaDirectDebitAccount->default);
        $this->assertEquals('1234', $sepaDirectDebitAccount->last4);
        $this->assertEquals('a-fake-bank-reference-token', $sepaDirectDebitAccount->bankReferenceToken);
        $this->assertEquals('RECURRENT', $sepaDirectDebitAccount->mandateType);
        $this->assertEquals('DateTime', get_class($sepaDirectDebitAccount->createdAt));
        $this->assertEquals('DateTime', get_class($sepaDirectDebitAccount->updatedAt));
    }

    public function testCreate_fromUnvalidatedCreditCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => [
                    'validate' => false
                ]
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
        $this->assertNotNull($result->paymentMethod->token);
    }

    public function testCreate_fromUnvalidatedFuturePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertSame('jane.doe@example.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
    }

    public function testCreate_fromOrderPaymentPaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'intent' => 'order',
                'payment_token' => 'paypal-payment-token',
                'payer_id' => 'paypal-payer-id',
                'token' => $paymentMethodToken,
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertSame('bt_buyer_us@paypal.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
        $this->assertNotNull($result->paymentMethod->payerId);
    }

    public function testCreate_fromOrderPaymentPaypalAccountNonceWithPayPalOptionsSnakeCase()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'intent' => 'order',
                'payment_token' => 'paypal-payment-token',
                'payer_id' => 'paypal-payer-id',
                'token' => $paymentMethodToken,
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'paypal' => [
                    'payee_email' => 'payee@example.com',
                    'order_id' => 'merchant-order-id',
                    'custom_field' => 'custom merchant field',
                    'description' => 'merchant description',
                    'amount' => '1.23',
                ]
            ],
        ]);

        $this->assertSame('bt_buyer_us@paypal.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
        $this->assertNotNull($result->paymentMethod->payerId);
    }

    public function testCreate_fromOrderPaymentPaypalAccountNonceWithPayPalOptionsCamelCase()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'intent' => 'order',
                'payment_token' => 'paypal-payment-token',
                'payer_id' => 'paypal-payer-id',
                'token' => $paymentMethodToken,
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'paypal' => [
                    'payeeEmail' => 'payee@example.com',
                    'orderId' => 'merchant-order-id',
                    'customField' => 'custom merchant field',
                    'description' => 'merchant description',
                    'amount' => '1.23',
                    'shipping' => [
                        'firstName' => 'Andrew',
                        'lastName' => 'Mason',
                        'company' => 'Braintree',
                        'streetAddress' => '456 W Main St',
                        'extendedAddress' => 'Apt 2F',
                        'locality' => 'Bartlett',
                        'region' => 'IL',
                        'postalCode' => '60103',
                        'countryName' => 'United States of America',
                        'countryCodeAlpha2' => 'US',
                        'countryCodeAlpha3' => 'USA',
                        'countryCodeNumeric' => '840'
                    ],
                ],
            ],
        ]);

        $this->assertSame('bt_buyer_us@paypal.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
        $this->assertNotNull($result->paymentMethod->payerId);
    }

    public function testCreate_fromPayPalRefreshToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paypalRefreshToken' => 'PAYPAL_REFRESH_TOKEN',
        ]);

        $this->assertSame($customer->id, $result->paymentMethod->customerId);
        $this->assertSame("B_FAKE_ID", $result->paymentMethod->billingAgreementId);
        $this->assertNotNull($result->paymentMethod->payerId);
    }

    public function testCreate_fromAbstractPaymentMethodNonce()
    {
        $customer = Braintree\Customer::createNoValidate();

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$abstractTransactable,
        ]);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertSame($customer->id, $result->paymentMethod->customerId);
    }

    public function testCreate_doesNotWorkForUnvalidatedOnetimePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paymentMethodToken
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertContains(
            Braintree\Error\Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT,
            array_map(function ($error) {
                return $error->code;
            }, $errors)
        );
    }

    public function testCreate_handlesValidationErrorsForPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'token' => $paymentMethodToken
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertContains(
            Braintree\Error\Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT,
            array_map(function ($error) {
                return $error->code;
            }, $errors)
        );
        $this->assertContains(
            Braintree\Error\Codes::PAYPAL_ACCOUNT_CONSENT_CODE_OR_ACCESS_TOKEN_IS_REQUIRED,
            array_map(function ($error) {
                return $error->code;
            }, $errors)
        );
    }

    public function testCreate_allowsPassingDefaultOptionWithNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card1 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $this->assertTrue($card1->isDefault());

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => [
                    'validate' => false
                ]
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'makeDefault' => true
            ]
        ]);

        $card2 = $result->paymentMethod;
        $card1 = Braintree\CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    public function testCreate_overridesNonceToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $firstToken = 'FIRST_TOKEN-' . strval(rand());
        $secondToken = 'SECOND_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'token' => $firstToken,
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => [
                    'validate' => false
                ]
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $secondToken
        ]);

        $card = $result->paymentMethod;
        $this->assertEquals($secondToken, $card->token);
    }

    public function testCreateWithVerificationAmount()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4000111111111115',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ]
        ]);
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => [
                'verifyCard' => 'true',
                'verificationAmount' => '5.00',
            ]
        ]);

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
    }

    public function testCreate_respectsVerifyCardAndVerificationMerchantAccountIdWhenIncludedOutsideOfTheNonce()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4000111111111115',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ]
        ]);
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => [
                'verifyCard' => 'true',
                'verificationMerchantAccountId' => Test\Helper::nonDefaultMerchantAccountId(),
            ]
        ]);

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals(Test\Helper::nonDefaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    public function testCreate_respectsFailOnDuplicatePaymentMethodWhenIncludedOutsideTheNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($result->success);

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationDate' => "05/2012"
            ]
        ]);
        $updateResult = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => [
                'failOnDuplicatePaymentMethod' => 'true',
            ]
        ]);

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals("81724", $resultErrors[0]->code);
    }

    public function testCreate_includesRiskDataWhenSkipAdvancedFraudCheckingIsFalse()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $http = new HttpClientApi($gateway->config);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ],
        ]);

        $result = $gateway->paymentMethod()->create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => true,
                'skipAdvancedFraudChecking' => false
            ],
        ]);

        $this->assertTrue($result->success);
        $verification = $result->paymentMethod->verification;
        $this->assertNotNull($verification->riskData);
    }

    public function testCreate_doesNotIncludeRiskDataWhenSkipAdvancedFraudCheckingIsTrue()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $http = new HttpClientApi($gateway->config);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ],
        ]);

        $result = $gateway->paymentMethod()->create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => true,
                'skipAdvancedFraudChecking' => true
            ],
        ]);

        $this->assertTrue($result->success);
        $verification = $result->paymentMethod->verification;
        $this->assertNull($verification->riskData);
    }

    public function testCreate_allowsPassingABillingAddressOutsideOfTheNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => [
                    'validate' => false
                ]
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => [
                'streetAddress' => '123 Abc Way'
            ]
        ]);

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
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => [
                    'validate' => false
                ],
                'billingAddress' => [
                    'streetAddress' => '456 Xyz Way'
                ]
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => [
                'streetAddress' => '123 Abc Way'
            ]
        ]);

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
        $nonce = $http->nonce_for_new_card([
            'customerId' => $customer->id,
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'billingAddress' => [
                    'streetAddress' => '456 Xyz Way'
                ]
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => [
                'streetAddress' => '123 Abc Way'
            ]
        ]);

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
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => [
                    'validate' => false
                ]
            ]
        ]);

        $address = Braintree\Address::create([
            'customerId' => $customer->id,
            'firstName' => 'Bobby',
            'lastName' => 'Tables'
        ])->address;
        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddressId' => $address->id
        ]);

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
        $originalToken = 'paypal-account-' . strval(rand());
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPaypalAccount([
            'paypalAccount' => [
                'consentCode' => 'consent-code',
                'token' => $originalToken
            ]
        ]);

        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => [
                'verifyCard' => 'true',
                'failOnDuplicatePaymentMethod' => 'true',
                'verificationMerchantAccountId' => 'Not a Real Merchant Account Id'
            ]
        ]);

        $this->assertTrue($result->success);
    }

    public function testCreate_ignoresPassedBillingAddressParamsForPaypalAccount()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPaypalAccount([
            'paypalAccount' => [
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            ]
        ]);
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => [
                'streetAddress' => '123 Abc Way'
            ]
        ]);

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = Braintree\PaypalAccount::find($token);
        $this->assertTrue(null != $foundPaypalAccount);
    }

    public function testCreate_ignoresPassedBillingAddressIdForPaypalAccount()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPaypalAccount([
            'paypalAccount' => [
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            ]
        ]);
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddressId' => 'address_id'
        ]);

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod, 'Braintree\PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = Braintree\PaypalAccount::find($token);
        $this->assertTrue(null != $foundPaypalAccount);
    }

    public function testCreate_acceptsNumberAndOtherCreditCardParameters()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$transactable,
            'cardholderName' => 'Jane Doe',
            'cvv' => '123',
            'expirationMonth' => '10',
            'expirationYear' => '24',
            'number' => '4242424242424242'
        ]);

        $this->assertTrue($result->success);
        $this->assertTrue('Jane Doe' == $result->paymentMethod->cardholderName);
        $this->assertTrue('10' == $result->paymentMethod->expirationMonth);
        $this->assertTrue('2024' == $result->paymentMethod->expirationYear);
        $this->assertTrue('424242' == $result->paymentMethod->bin);
        $this->assertTrue('4242' == $result->paymentMethod->last4);
    }

    public function testCreate_acceptAccountTypeCredit()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => Braintree\Test\CreditCardNumbers::$hiper,
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ],
            'share' => true
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'hiper_brl',
                'verificationAccountType' => 'credit'
            ]
        ]);

        $this->assertSame('credit', $result->paymentMethod->verification->creditCard['accountType']);
    }

    public function testCreate_acceptAccountTypeDebit()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => Braintree\Test\CreditCardNumbers::$hiper,
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ],
            'share' => true
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'hiper_brl',
                'verificationAccountType' => 'debit'
            ]
        ]);

        $this->assertSame('debit', $result->paymentMethod->verification->creditCard['accountType']);
    }

    public function testUpdate_acceptAccountTypeCredit()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree\Test\CreditCardNumbers::$hiper,
            'expirationDate' => '06/2013',
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'hiper_brl',
                'verificationAccountType' => 'credit'
            ]
        ]);

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $this->assertSame('credit', $updateResult->paymentMethod->verification->creditCard['accountType']);
    }

    public function testUpdate_acceptAccountTypeDebit()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree\Test\CreditCardNumbers::$hiper,
            'expirationDate' => '06/2013',
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'hiper_brl',
                'verificationAccountType' => 'debit'
            ]
        ]);

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $this->assertSame('debit', $updateResult->paymentMethod->verification->creditCard['accountType']);
    }

    public function testUpdate_fromThreeDSecureNonceWithInvalidPassThruParams()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;
        $nonce = Braintree\Test\Nonces::$transactable;

        $result = Braintree\PaymentMethod::update($creditCard->token, [
            'paymentMethodNonce' => $nonce,
            'threeDSecurePassThru' => [
                'eciFlag' => '02',
                'cavv' => 'some_cavv',
                'xid' => 'some_xid',
                'threeDSecureVersion' => 'xx',
                'authenticationResponse' => 'Y',
                'directoryResponse' => 'Y',
                'cavvAlgorithm' => '2',
                'dsTransactionId' => 'some_ds_transaction_id',
            ],
            'options' => [
                'verifyCard' => true,
            ]
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('verification')->onAttribute('threeDSecureVersion');
        $this->assertEquals(Braintree\Error\Codes::VERIFICATION_THREE_D_SECURE_THREE_D_SECURE_VERSION_IS_INVALID, $errors[0]->code);
        $this->assertEquals(1, preg_match('/The version of 3D Secure authentication must be composed only of digits and separated by periods/', $result->message));
    }

    public function testUpdate_fromThreeDSecureNonceWithPassThruParams()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;
        $nonce = Braintree\Test\Nonces::$transactable;

        $result = Braintree\PaymentMethod::update($creditCard->token, [
            'paymentMethodNonce' => $nonce,
            'threeDSecurePassThru' => [
                'eciFlag' => '02',
                'cavv' => 'some_cavv',
                'xid' => 'some_xid',
                'threeDSecureVersion' => '1.1.1',
                'authenticationResponse' => 'Y',
                'directoryResponse' => 'Y',
                'cavvAlgorithm' => '2',
                'dsTransactionId' => 'some_ds_transaction_id',
            ],
            'options' => [
                'verifyCard' => true,
            ]
        ]);

        $this->assertTrue($result->success);
    }

    public function testUpdate_includesRiskDataWhenSkipAdvancedFraudCheckingIsFalse()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $creditCard = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
        ])->creditCard;

        $result = $gateway->paymentMethod()->update($creditCard->token, [
            'expirationDate' => '06/2023',
            'options' => [
                'verifyCard' => true,
                'skipAdvancedFraudChecking' => false
            ],
        ]);

        $this->assertTrue($result->success);
        $verification = $result->paymentMethod->verification;
        $this->assertNotNull($verification->riskData);
    }

    public function testUpdate_doesNotIncludeRiskDataWhenSkipAdvancedFraudCheckingIsTrue()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $creditCard = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
        ])->creditCard;

        $result = $gateway->paymentMethod()->update($creditCard->token, [
            'expirationDate' => '06/2023',
            'options' => [
                'verifyCard' => true,
                'skipAdvancedFraudChecking' => true
            ],
        ]);

        $this->assertTrue($result->success);
        $verification = $result->paymentMethod->verification;
        $this->assertNull($verification->riskData);
    }

    public function testCreate_ErrorsWithVerificationAccountTypeIsInvalid()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => Braintree\Test\CreditCardNumbers::$hiper,
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ],
            'share' => true
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'hiper_brl',
                'verificationAccountType' => 'wrong'
            ]
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->forKey('options')->onAttribute('verificationAccountType');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_OPTIONS_VERIFICATION_ACCOUNT_TYPE_IS_INVALID, $errors[0]->code);
    }

    public function testCreate_ErrorsWithVerificationAccountTypeNotSupported()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            'credit_card' => [
                'number' => '5105105105105100',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            ],
            'share' => true
        ]);

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => true,
                'verificationAccountType' => 'wrong'
            ]
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->forKey('options')->onAttribute('verificationAccountType');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_OPTIONS_VERIFICATION_ACCOUNT_TYPE_NOT_SUPPORTED, $errors[0]->code);
    }

    public function testFind_returnsCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken
        ]);
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
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
        ]);
        $this->assertTrue($creditCardResult->success);

        $subscriptionId = strval(rand());
        Braintree\Subscription::create([
            'id' => $subscriptionId,
            'paymentMethodToken' => $creditCardResult->creditCard->token,
            'planId' => 'integration_trialless_plan',
            'price' => '1.00'
        ]);

        $foundCreditCard = Braintree\PaymentMethod::find($creditCardResult->creditCard->token);
        $this->assertEquals($subscriptionId, $foundCreditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $foundCreditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $foundCreditCard->subscriptions[0]->price);
    }

    public function testFind_returnsPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            ]
        ]);

        Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $foundPayPalAccount = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
    }

    public function testFind_returnsApplePayCards()
    {
        $paymentMethodToken = 'APPLE_PAY-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $nonce = Braintree\Test\Nonces::$applePayVisa;
        Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken
        ]);

        $foundApplePayCard = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundApplePayCard->token);
        $this->assertInstanceOf('Braintree\ApplePayCard', $foundApplePayCard);
        $this->assertTrue(intval($foundApplePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundApplePayCard->expirationYear) > 0);
    }

    public function testFind_returnsGooglePayCards()
    {
        $paymentMethodToken = 'GOOGLE-PAY-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $nonce = Braintree\Test\Nonces::$googlePay;
        Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken
        ]);

        $foundGooglePayCard = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundGooglePayCard->token);
        $this->assertInstanceOf('Braintree\GooglePayCard', $foundGooglePayCard);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $foundGooglePayCard->virtualCardType);
        $this->assertSame("1117", $foundGooglePayCard->virtualCardLast4);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $foundGooglePayCard->sourceCardType);
        $this->assertSame("1111", $foundGooglePayCard->sourceCardLast4);
        $this->assertSame($customer->id, $foundGooglePayCard->customerId);
        $this->assertTrue($foundGooglePayCard->default);
        $this->assertStringContainsString('android_pay', $foundGooglePayCard->imageUrl);
        $this->assertTrue(intval($foundGooglePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundGooglePayCard->expirationYear) > 0);
        $this->assertFalse($foundGooglePayCard->isNetworkTokenized);
    }

    public function testFind_returnsAbstractPaymentMethods()
    {
        $paymentMethodToken = 'ABSTRACT-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $nonce = Braintree\Test\Nonces::$abstractTransactable;
        Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken
        ]);

        $foundPaymentMethod = Braintree\PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundPaymentMethod-> token);
    }

    public function testFind_throwsIfCannotBeFound()
    {
        $this->expectException('Braintree\Exception\NotFound');
        Braintree\PaymentMethod::find('NON_EXISTENT_TOKEN');
    }

    public function testUpdate_updatesTheCreditCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree\Test\CreditCardNumbers::$masterCard,
            'expirationDate' => "06/2013"
        ]);

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("New Holder", $updatedCreditCard->cardholderName);
        $this->assertSame(substr(Braintree\Test\CreditCardNumbers::$masterCard, 0, 6), $updatedCreditCard->bin);
        $this->assertSame(substr(Braintree\Test\CreditCardNumbers::$masterCard, -4), $updatedCreditCard->last4);
        $this->assertSame("06/2013", $updatedCreditCard->expirationDate);
    }

    public function testUpdate_updatesTheCreditCardWith3DSPassThru()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'threeDSecurePassThru' => [
                'eciFlag' => '02',
                'cavv' => 'some_cavv',
                'xid' => 'some_xid',
                'threeDSecureVersion' => '1.0.2',
                'authenticationResponse' => 'Y',
                'directoryResponse' => 'Y',
                'cavvAlgorithm' => '2',
                'dsTransactionId' => 'validDsTransactionId'
            ],
        ]);

        // nothing we can really assert on here other than it was a success
        $this->assertTrue($updateResult->success);
    }

    public function testUpdate_createsANewBillingAddressByDefault()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => [
                'streetAddress' => '123 Nigeria Ave'
            ]
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'billingAddress' => [
                'region' => 'IL'
            ]
        ]);

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("IL", $updatedCreditCard->billingAddress->region);
        $this->assertSame(null, $updatedCreditCard->billingAddress->streetAddress);
        $this->assertFalse($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    public function testUpdate_updatesTheBillingAddressIfOptionIsSpecified()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => [
                'streetAddress' => '123 Nigeria Ave'
            ]
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'billingAddress' => [
                'region' => 'IL',
                'options' => [
                    'updateExisting' => 'true'
                ]
            ],
        ]);

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("IL", $updatedCreditCard->billingAddress->region);
        $this->assertSame("123 Nigeria Ave", $updatedCreditCard->billingAddress->streetAddress);
        $this->assertTrue($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    public function testUpdate_updatesTheCountryViaCodes()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => [
                'streetAddress' => '123 Nigeria Ave'
            ]
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'billingAddress' => [
                'countryName' => 'American Samoa',
                'countryCodeAlpha2' => 'AS',
                'countryCodeAlpha3' => 'ASM',
                'countryCodeNumeric' => '016',
                'options' => [
                    'updateExisting' => 'true'
                ]
            ],
        ]);

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("American Samoa", $updatedCreditCard->billingAddress->countryName);
        $this->assertSame("AS", $updatedCreditCard->billingAddress->countryCodeAlpha2);
        $this->assertSame("ASM", $updatedCreditCard->billingAddress->countryCodeAlpha3);
        $this->assertSame("016", $updatedCreditCard->billingAddress->countryCodeNumeric);
    }

    public function testUpdate_canPassExpirationMonthAndExpirationYear()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'number' => Braintree\Test\CreditCardNumbers::$masterCard,
            'expirationMonth' => "07",
            'expirationYear' => "2011"
        ]);

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("07", $updatedCreditCard->expirationMonth);
        $this->assertSame("07", $updatedCreditCard->expirationMonth);
        $this->assertSame("07/2011", $updatedCreditCard->expirationDate);
    }

    public function testUpdate_verifiesTheUpdateIfOptionsVerifyCardIsTrue()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree\Test\CreditCardNumbers::$failsSandboxVerification['MasterCard'],
            'expirationDate' => "06/2013",
            'options' => [
                'verifyCard' => 'true'
            ]
        ]);

        $this->assertFalse($updateResult->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $updateResult->creditCardVerification->status);
        $this->assertEquals(null, $updateResult->creditCardVerification->gatewayRejectionReason);
    }

    public function testUpdate_canPassCustomVerificationAmount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Card Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2020"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'paymentMethodNonce' => Braintree\Test\Nonces::$processorDeclinedMasterCard,
            'options' => [
                'verifyCard' => 'true',
                'verificationAmount' => '2.34'
            ]
        ]);

        $this->assertFalse($updateResult->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $updateResult->creditCardVerification->status);
        $this->assertEquals(null, $updateResult->creditCardVerification->gatewayRejectionReason);
    }

    public function testUpdate_canUpdateTheBillingAddress()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => [
                'firstName' => 'Old First Name',
                'lastName' => 'Old Last Name',
                'company' => 'Old Company',
                'streetAddress' => '123 Old St',
                'extendedAddress' => 'Apt Old',
                'locality' => 'Old City',
                'region' => 'Old State',
                'postalCode' => '12345',
                'countryName' => 'Canada'
            ]
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'billingAddress' => [
                'firstName' => 'New First Name',
                'lastName' => 'New Last Name',
                'company' => 'New Company',
                'streetAddress' => '123 New St',
                'extendedAddress' => 'Apt New',
                'locality' => 'New City',
                'region' => 'New State',
                'postalCode' => '56789',
                'countryName' => 'United States of America'
            ]
        ]);

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
        $creditCardResult = Braintree\CreditCard::create([
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree\PaymentMethod::update($creditCard->token, [
            'cardholderName' => 'New Holder',
            'number' => 'invalid',
            'expirationDate' => "05/2014",
        ]);

        $this->assertFalse($updateResult->success);
        $numberErrors = $updateResult->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals("Credit card number must be 12-19 digits.", $numberErrors[0]->message);
    }

    public function testUpdate_canUpdateTheDefault()
    {
        $customer = Braintree\Customer::createNoValidate();

        $creditCardResult1 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2009"
        ]);
        $this->assertTrue($creditCardResult1->success);
        $creditCard1 = $creditCardResult1->creditCard;

        $creditCardResult2 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => "05/2009"
        ]);
        $this->assertTrue($creditCardResult2->success);
        $creditCard2 = $creditCardResult2->creditCard;

        $this->assertTrue($creditCard1->default);
        $this->assertFalse($creditCard2->default);


        $updateResult = Braintree\PaymentMethod::update($creditCard2->token, [
            'options' => [
                'makeDefault' => 'true'
            ]
        ]);
        $this->assertTrue($updateResult->success);

        $this->assertFalse(Braintree\PaymentMethod::find($creditCard1->token)->default);
        $this->assertTrue(Braintree\PaymentMethod::find($creditCard2->token)->default);
    }

    public function testUpdate_updatesAPaypalAccountsToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $originalToken = 'paypal-account-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'consent-code',
                'token' => $originalToken
            ]
        ]);

        $originalResult = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id
        ]);
        $this->assertTrue($originalResult->success);

        $originalPaypalAccount = $originalResult->paymentMethod;

        $updatedToken = 'UPDATED_TOKEN-' . strval(rand());
        $updateResult = Braintree\PaymentMethod::update($originalPaypalAccount->token, [
            'token' => $updatedToken
        ]);
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = Braintree\PaymentMethod::find($updatedToken);
        $this->assertEquals($originalPaypalAccount->email, $updatedPaypalAccount->email);

        $this->expectException('Braintree\Exception\NotFound', 'payment method with token ' . $originalToken . ' not found');
        Braintree\PaymentMethod::find($originalToken);
    }

    public function testUpdate_canMakeAPaypalAccountTheDefaultPaymentMethod()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => Braintree\Test\CreditCardNumbers::$visa,
            'expirationDate' => '05/2009',
            'options' => [
                'makeDefault' => 'true'
            ]
        ]);
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'consent-code',
            ]
        ]);

        $originalToken = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id
        ])->paymentMethod->token;

        $updateResult = Braintree\PaymentMethod::update($originalToken, [
            'options' => [
                'makeDefault' => 'true'
            ]
        ]);
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = Braintree\PaymentMethod::find($originalToken);
        $this->assertTrue($updatedPaypalAccount->default);
    }

    public function testUpdate_returnsAnErrorIfATokenForAccountIsUsedToAttemptAnUpdate()
    {
        $customer = Braintree\Customer::createNoValidate();
        $firstToken = 'paypal-account-' . strval(rand());
        $secondToken = 'paypal-account-' . strval(rand());

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $firstNonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'consent-code',
                'token' => $firstToken
            ]
        ]);
        $firstResult = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $firstNonce,
            'customerId' => $customer->id
        ]);
        $this->assertTrue($firstResult->success);
        $firstPaypalAccount = $firstResult->paymentMethod;

        $http = new HttpClientApi(Braintree\Configuration::$global);
        $secondNonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'consent-code',
                'token' => $secondToken
            ]
        ]);
        $secondResult = Braintree\PaymentMethod::create([
            'paymentMethodNonce' => $secondNonce,
            'customerId' => $customer->id
        ]);
        $this->assertTrue($secondResult->success);
        $secondPaypalAccount = $firstResult->paymentMethod;


        $updateResult = Braintree\PaymentMethod::update($firstToken, [
            'token' => $secondToken
        ]);

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals("92906", $resultErrors[0]->code);
    }

    public function testDelete_worksWithCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $creditCardResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken
        ]);
        $this->assertTrue($creditCardResult->success);

        Braintree\PaymentMethod::delete($paymentMethodToken);

        $this->expectException('Braintree\Exception\NotFound');
        Braintree\PaymentMethod::find($paymentMethodToken);
        self::integrationMerchantConfig();
    }

    public function testDelete_worksWithPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonceForPayPalAccount([
            'paypal_account' => [
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            ]
        ]);

        $paypalAccountResult = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);
        $this->assertTrue($paypalAccountResult->success);

        Braintree\PaymentMethod::delete($paymentMethodToken, ['revokeAllGrants' => false]);

        $this->expectException('Braintree\Exception\NotFound');
        Braintree\PaymentMethod::find($paymentMethodToken);
    }

    public function testGrant_returnsASingleUseNonce()
    {
        $partnerMerchantGateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_public_id',
            'publicKey' => 'oauth_app_partner_user_public_key',
            'privateKey' => 'oauth_app_partner_user_private_key'
        ]);

        $customer = $partnerMerchantGateway->customer()->create([
            'firstName' => 'Joe',
            'lastName' => 'Brown'
        ])->customer;
        $creditCard = $partnerMerchantGateway->creditCard()->create([
            'customerId' => $customer->id,
            'cardholderName' => 'Adam Davis',
            'number' => '4111111111111111',
            'expirationDate' => '05/2009'
        ])->creditCard;

        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $grantResult = $grantingGateway->paymentMethod()->grant($creditCard->token);
        $this->assertTrue($grantResult->success);

        $result = Braintree\Transaction::sale([
            'amount' => '100.00',
            'paymentMethodNonce' => $grantResult->paymentMethodNonce->nonce
        ]);
        $this->assertTrue($result->success);

        $secondResult = Braintree\Transaction::sale([
            'amount' => '100.00',
            'paymentMethodNonce' => $grantResult->paymentMethodNonce->nonce
        ]);
        $this->assertFalse($secondResult->success);
    }

    public function testGrant_returnsANonceThatIsNotVaultable()
    {
        $partnerMerchantGateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_public_id',
            'publicKey' => 'oauth_app_partner_user_public_key',
            'privateKey' => 'oauth_app_partner_user_private_key'
        ]);

        $customer = $partnerMerchantGateway->customer()->create([
            'firstName' => 'Joe',
            'lastName' => 'Brown'
        ])->customer;
        $creditCard = $partnerMerchantGateway->creditCard()->create([
            'customerId' => $customer->id,
            'cardholderName' => 'Adam Davis',
            'number' => '4111111111111111',
            'expirationDate' => '05/2009'
        ])->creditCard;

        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $grantResult = $grantingGateway->paymentMethod()->grant($creditCard->token, ['allow_vaulting' => false]);

        $customer = $partnerMerchantGateway->customer()->create([
            'firstName' => 'Bob',
            'lastName' => 'Rob'
        ])->customer;
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $grantResult->paymentMethodNonce->nonce
        ]);
        $this->assertFalse($result->success);
    }

    public function testGrant_returnsANonceThatIsVaultableSnakeCase()
    {
        $partnerMerchantGateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_public_id',
            'publicKey' => 'oauth_app_partner_user_public_key',
            'privateKey' => 'oauth_app_partner_user_private_key'
        ]);

        $customer = $partnerMerchantGateway->customer()->create([
            'firstName' => 'Joe',
            'lastName' => 'Brown'
        ])->customer;
        $creditCard = $partnerMerchantGateway->creditCard()->create([
            'customerId' => $customer->id,
            'cardholderName' => 'Adam Davis',
            'number' => '4111111111111111',
            'expirationDate' => '05/2009'
        ])->creditCard;

        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $grantResult = $grantingGateway->paymentMethod()->grant($creditCard->token, ['allow_vaulting' => true]);

        $customer = Braintree\Customer::create([
            'firstName' => 'Bob',
            'lastName' => 'Rob'
        ])->customer;
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $grantResult->paymentMethodNonce->nonce
        ]);
        $this->assertTrue($result->success);
    }

    public function testGrant_returnsANonceThatIsVaultableCamelCase()
    {
        $partnerMerchantGateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_public_id',
            'publicKey' => 'oauth_app_partner_user_public_key',
            'privateKey' => 'oauth_app_partner_user_private_key'
        ]);

        $customer = $partnerMerchantGateway->customer()->create([
            'firstName' => 'Joe',
            'lastName' => 'Brown'
        ])->customer;
        $creditCard = $partnerMerchantGateway->creditCard()->create([
            'customerId' => $customer->id,
            'cardholderName' => 'Adam Davis',
            'number' => '4111111111111111',
            'expirationDate' => '05/2009'
        ])->creditCard;

        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $grantResult = $grantingGateway->paymentMethod()->grant($creditCard->token, ['allowVaulting' => true, 'includeBillingPostalCode' => true]);

        $customer = Braintree\Customer::create([
            'firstName' => 'Bob',
            'lastName' => 'Rob'
        ])->customer;
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $grantResult->paymentMethodNonce->nonce
        ]);
        $this->assertTrue($result->success);
    }

    public function testGrant_raisesAnErrorIfTokenIsNotFound()
    {
        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $this->expectException('Braintree\Exception\NotFound');
        $grantResult = $grantingGateway->paymentMethod()->grant("not_a_real_token", false);
    }

    public function testRevoke_rendersANonceUnusable()
    {
        $partnerMerchantGateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_public_id',
            'publicKey' => 'oauth_app_partner_user_public_key',
            'privateKey' => 'oauth_app_partner_user_private_key'
        ]);

        $customer = $partnerMerchantGateway->customer()->create([
            'firstName' => 'Joe',
            'lastName' => 'Brown'
        ])->customer;
        $creditCard = $partnerMerchantGateway->creditCard()->create([
            'customerId' => $customer->id,
            'cardholderName' => 'Adam Davis',
            'number' => '4111111111111111',
            'expirationDate' => '05/2009'
        ])->creditCard;

        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $grantResult = $grantingGateway->paymentMethod()->grant($creditCard->token, false);
        $revokeResult = $grantingGateway->paymentMethod()->revoke($creditCard->token);
        $this->assertTrue($revokeResult->success);

        $result = Braintree\Transaction::sale([
            'amount' => '100.00',
            'paymentMethodNonce' => $grantResult->paymentMethodNonce->nonce
        ]);
        $this->assertFalse($result->success);
    }

    public function testRevoke_raisesAnErrorIfTokenIsNotFound()
    {
        $oauthAppGateway = new Braintree\Gateway([
            'clientId' =>  'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($oauthAppGateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'grant_payment_method'
        ]);

        $credentials = $oauthAppGateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $grantingGateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $this->expectException('Braintree\Exception\NotFound');
        $grantResult = $grantingGateway->paymentMethod()->revoke("not_a_real_token");
    }
}
