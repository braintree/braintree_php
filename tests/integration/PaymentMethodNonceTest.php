<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class PaymentMethodNonceTest extends Setup
{
    const INDIAN_PAYMENT_TOKEN = 'india_visa_credit';
    const EUROPEAN_PAYMENT_TOKEN = 'european_visa_credit';
    const INDIAN_MERCHANT_TOKEN = 'india_three_d_secure_merchant_account';
    const EUROPEAN_MERCHANT_TOKEN = 'european_three_d_secure_merchant_account';
    const AMOUNT_THRESHOLD_FOR_RBI = 2000;

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

    public function testCreateNonce_fromPaymentMethodTokenWithInvalidParams()
    {
        $params = [
            "paymentMethodNonce" => [
                "merchantAccountId" => self::INDIAN_MERCHANT_TOKEN,
                "authenticationInsight" => true,
                "invalidKey" => "foo"
            ]
        ];

        $this->expectException('InvalidArgumentException');
        $result = Braintree\PaymentMethodNonce::create(self::INDIAN_PAYMENT_TOKEN, $params);
    }

    public function testCreateNonce_withAuthInsightRegulationEnvironmentUnavailable()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        ])->creditCard;

        $params = [
            "paymentMethodNonce" => [
                "merchantAccountId" => self::INDIAN_MERCHANT_TOKEN,
                "authenticationInsight" => true,
                "authenticationInsightOptions" => [
                   "amount" => self::AMOUNT_THRESHOLD_FOR_RBI
                ]
            ]
        ];

        $result = Braintree\PaymentMethodNonce::create($card->token, $params);
        $authInsight = $result->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('unavailable', $authInsight['regulationEnvironment']);
    }

    public function testCreate_nonceWithAuthInsightRegulationEnvironmentUnregulated()
    {
        $authInsight = $this->_requestAuthenticationInsights(
            self::EUROPEAN_MERCHANT_TOKEN,
            self::INDIAN_PAYMENT_TOKEN
        )->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('unregulated', $authInsight['regulationEnvironment']);
    }

    public function testCreate_nonceWithAuthInsightRegulationEnvironmentPsd2()
    {
        $authInsight = $this->_requestAuthenticationInsights(
            self::EUROPEAN_MERCHANT_TOKEN,
            self::EUROPEAN_PAYMENT_TOKEN
        )->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('psd2', $authInsight['regulationEnvironment']);
    }

    public function testCreate_nonceWithAuthInsightRegulationEnvironmentRbi()
    {
        $authInsight = $this->_requestAuthenticationInsights(
            self::INDIAN_MERCHANT_TOKEN,
            self::INDIAN_PAYMENT_TOKEN,
            self::AMOUNT_THRESHOLD_FOR_RBI
        )->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('rbi', $authInsight['regulationEnvironment']);
    }

    public function testCreate_nonceWithAuthInsightScaIndicatorUnavailableWithoutAmount()
    {
        $authInsight = $this->_requestAuthenticationInsights(
            self::INDIAN_MERCHANT_TOKEN,
            self::INDIAN_PAYMENT_TOKEN
        )->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('unavailable', $authInsight['scaIndicator']);
    }

    public function testCreate_nonceWithAuthInsightScaIndicatorScaRequired()
    {
        $authInsight = $this->_requestAuthenticationInsights(
            self::INDIAN_MERCHANT_TOKEN,
            self::INDIAN_PAYMENT_TOKEN,
            self::AMOUNT_THRESHOLD_FOR_RBI + 1
        )->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('sca_required', $authInsight['scaIndicator']);
    }

    public function testCreate_nonceWithAuthInsightScaIndicatorScaOptional()
    {
        $authInsight = $this->_requestAuthenticationInsights(
            self::INDIAN_MERCHANT_TOKEN,
            self::INDIAN_PAYMENT_TOKEN,
            1000,
            true,
            1500
        )->paymentMethodNonce->authenticationInsight;
        $this->assertEquals('sca_optional', $authInsight['scaIndicator']);
    }

    protected function _requestAuthenticationInsights($merchantToken, $paymentToken, $amount = null, $recurringCustomerConsent = null, $recurringMaxAmount = null)
    {
        $params = [
            "paymentMethodNonce" => [
                "merchantAccountId" => $merchantToken,
                "authenticationInsight" => true,
                "authenticationInsightOptions" => [
                    "amount" => $amount,
                    "recurringCustomerConsent" => $recurringCustomerConsent,
                    "recurringMaxAmount" => $recurringMaxAmount,
                ]
            ]
        ];

        $result = Braintree\PaymentMethodNonce::create($paymentToken, $params);
        return $result;
    }

    public function testCreate_fromNonExistentPaymentMethodToken()
    {
        $this->expectException('Braintree\Exception\NotFound');
        Braintree\PaymentMethodNonce::create('not_a_token');
    }

    public function testFind_exposesPayPalDetails()
    {
        $foundNonce = Braintree\PaymentMethodNonce::find('fake-paypal-one-time-nonce');
        $details = $foundNonce->details;

        $this->assertNotNull($details['payerInfo']['firstName']);
        $this->assertNotNull($details['payerInfo']['email']);
        $this->assertNotNull($details['payerInfo']['payerId']);

        $this->assertNotNull($details['cobrandedCardLabel']);
        $this->assertNotNull($details['shippingAddress']);
        $this->assertNotNull($details['billingAddress']);
        $this->assertNotNull($details['shippingOptionId']);
    }

    public function testFind_exposesVenmoDetails()
    {
        $foundNonce = Braintree\PaymentMethodNonce::find('fake-venmo-account-nonce');
        $details = $foundNonce->details;

        $this->assertEquals('99', $details['lastTwo']);
        $this->assertEquals('venmojoe', $details['username']);
        $this->assertEquals('1234567891234567891', $details['venmoUserId']);
    }

    public function testFind_exposesSepaDirectDebitAccountDetails()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$sepaDirectDebit);
        $details = $nonce->details;

        $this->assertEquals('1234', $details['ibanLastChars']);
        $this->assertEquals('a-fake-mp-customer-id', $details['merchantOrPartnerCustomerId']);
        $this->assertEquals('a-fake-bank-reference-token', $details['bankReferenceToken']);
        $this->assertEquals('RECURRENT', $details['mandateType']);
    }

    public function testFind_exposesThreeDSecureInfo()
    {
        $nonce = 'fake-three-d-secure-visa-full-authentication-nonce';
        $foundNonce = Braintree\PaymentMethodNonce::find($nonce);
        $info = $foundNonce->threeDSecureInfo;

        $this->assertEquals($nonce, $foundNonce->nonce);
        $this->assertEquals('CreditCard', $foundNonce->type);
        $this->assertEquals('authenticate_successful', $info->status);
        $this->assertIsString($info->enrolled);
        $this->assertIsBool($info->liabilityShifted);
        $this->assertIsBool($info->liabilityShiftPossible);
    }

    public function testFind_returnsBin()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableVisa);
        $this->assertEquals("401288", $nonce->details["bin"]);
    }

    public function testFind_exposesBinData()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableVisa);
        $this->assertEquals(Braintree\Test\Nonces::$transactableVisa, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $binData = $nonce->binData;
        $this->assertEquals(Braintree\CreditCard::COMMERCIAL_UNKNOWN, $binData->commercial);
        $this->assertEquals(Braintree\CreditCard::DEBIT_UNKNOWN, $binData->debit);
        $this->assertEquals(Braintree\CreditCard::DURBIN_REGULATED_UNKNOWN, $binData->durbinRegulated);
        $this->assertEquals(Braintree\CreditCard::HEALTHCARE_UNKNOWN, $binData->healthcare);
        $this->assertEquals(Braintree\CreditCard::PAYROLL_UNKNOWN, $binData->payroll);
        $this->assertEquals(Braintree\CreditCard::PREPAID_NO, $binData->prepaid);
        $this->assertEquals("Unknown", $binData->issuingBank);
        $this->assertEquals("Unknown", $binData->productId);
    }

    public function testFind_returnsBinDataForCommercialNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableCommercial);
        $this->assertEquals(Braintree\Test\Nonces::$transactableCommercial, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals(Braintree\CreditCard::COMMERCIAL_YES, $nonce->binData->commercial);
    }

    public function testFind_nonceShowsMetaCheckoutCardDetails()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$metaCheckoutCard);
        $details = $nonce->details;

        $this->assertEquals('401288', $details["bin"]);
        $this->assertEquals('81', $details["lastTwo"]);
        $this->assertEquals('1881', $details["lastFour"]);
        $this->assertEquals('Visa', $details["cardType"]);
        $this->assertEquals('Meta Checkout Card Cardholder', $details["cardholderName"]);
        $this->assertEquals(strval(date('Y') + 1), $details["expirationYear"]);
        $this->assertEquals('12', $details["expirationMonth"]);
    }

    public function testFind_nonceShowsMetaCheckoutTokenDetails()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$metaCheckoutToken);
        $details = $nonce->details;

        $this->assertEquals('401288', $details["bin"]);
        $this->assertEquals('81', $details["lastTwo"]);
        $this->assertEquals('1881', $details["lastFour"]);
        $this->assertEquals('Visa', $details["cardType"]);
        $this->assertEquals('Meta Checkout Token Cardholder', $details["cardholderName"]);
        $this->assertEquals(strval(date('Y') + 1), $details["expirationYear"]);
        $this->assertEquals('12', $details["expirationMonth"]);
    }

    public function testFind_returnsBinDataForDebitNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableDebit);
        $this->assertEquals(Braintree\Test\Nonces::$transactableDebit, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals(Braintree\CreditCard::DEBIT_YES, $nonce->binData->debit);
    }

    public function testFind_returnsBinDataForDurbinRegulatedNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableDurbinRegulated);
        $this->assertEquals(Braintree\Test\Nonces::$transactableDurbinRegulated, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals(Braintree\CreditCard::DURBIN_REGULATED_YES, $nonce->binData->durbinRegulated);
    }

    public function testFind_returnsBinDataForHealthcareNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableHealthcare);
        $this->assertEquals(Braintree\Test\Nonces::$transactableHealthcare, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals(Braintree\CreditCard::HEALTHCARE_YES, $nonce->binData->healthcare);
    }

    public function testFind_returnsBinDataForPayrollNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactablePayroll);
        $this->assertEquals(Braintree\Test\Nonces::$transactablePayroll, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals(Braintree\CreditCard::PAYROLL_YES, $nonce->binData->payroll);
    }

    public function testFind_returnsBinDataForPrepaidNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactablePrepaid);
        $this->assertEquals(Braintree\Test\Nonces::$transactablePrepaid, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals(Braintree\CreditCard::PREPAID_YES, $nonce->binData->prepaid);
    }

    public function testFind_returnsBinDataForCountryOfIssuanceNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableCountryOfIssuanceUSA);
        $this->assertEquals(Braintree\Test\Nonces::$transactableCountryOfIssuanceUSA, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals("USA", $nonce->binData->countryOfIssuance);
    }

    public function testFind_returnsBinDataForIssuingBankNonce()
    {
        $nonce = Braintree\PaymentMethodNonce::find(Braintree\Test\Nonces::$transactableIssuingBankNetworkOnly);
        $this->assertEquals(Braintree\Test\Nonces::$transactableIssuingBankNetworkOnly, $nonce->nonce);
        $this->assertEquals('CreditCard', $nonce->type);
        $this->assertNotNull($nonce->binData);
        $this->assertEquals("NETWORK ONLY", $nonce->binData->issuingBank);
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
        $this->expectException('Braintree\Exception\NotFound');
        Braintree\PaymentMethodNonce::find('not_a_nonce');
    }
}
