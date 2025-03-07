<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Braintree\CreditCardNumbers\CardTypeIndicators;
use Test\Setup;
use Braintree;

class CreditCardTest extends Setup
{
    public function testCreate()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
        $this->assertEquals(1, preg_match('/\A\w{32}\z/', $result->creditCard->uniqueNumberIdentifier));
        $this->assertEquals(1, preg_match('/png/', $result->creditCard->imageUrl));
    }

    public function testGatewayCreate()
    {
        $customer = Braintree\Customer::createNoValidate();

        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
        $result = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);

        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
    }

    public function testCreate_withDefault()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card1 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;
        $this->assertTrue($card1->isDefault());

        $card2 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'options' => [
                'makeDefault' => true
            ]
        ])->creditCard;

        $card1 = Braintree\CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    public function testCreateWithVerificationAmount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '4111111111111111',
            'expirationDate' => '05/12',
            'options' => [
                'verificationAmount' => '5.00',
                'verifyCard' => true
            ]
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('411111', $result->creditCard->bin);
        $this->assertEquals('1111', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
        $this->assertEquals(1, preg_match('/\A\w{32}\z/', $result->creditCard->uniqueNumberIdentifier));
        $this->assertEquals(1, preg_match('/png/', $result->creditCard->imageUrl));
    }

    public function testAddCardToExistingCustomerUsingNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            "credit_card" => [
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ],
            "share" => true
        ]);

        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertSame("411111", $result->creditCard->bin);
        $this->assertSame("1111", $result->creditCard->last4);
    }

    public function testCreate_withDeviceData()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'deviceData' => 'device_data',
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);

        $this->assertTrue($result->success);
    }

    public function testCreate_withExpirationMonthAndYear()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationMonth' => '05',
            'expirationYear' => '2011'
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2011', $result->creditCard->expirationDate);
    }

    public function testCreate_withSpecifyingToken()
    {
        $token = strval(rand());
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $token
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals($token, $result->creditCard->token);
        $this->assertEquals($token, Braintree\CreditCard::find($token)->token);
    }

    public function testCreate_withDuplicateCardCheck()
    {
        $customer = Braintree\Customer::createNoValidate();

        $attributes = [
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => ['failOnDuplicatePaymentMethod' => true]
        ];
        Braintree\CreditCard::create($attributes);

        $result = Braintree\CreditCard::create($attributes);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_DUPLICATE_CARD_EXISTS, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Duplicate card exists in the vault\./', $result->message));
    }

    public function testCreate_withDuplicateCardCheckForCustomer()
    {
        $customer = Braintree\Customer::createNoValidate();

        $attributes = [
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => ['failOnDuplicatePaymentMethodForCustomer' => true]
        ];
        Braintree\CreditCard::create($attributes);

        $result = Braintree\CreditCard::create($attributes);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_DUPLICATE_CARD_EXISTS_FOR_CUSTOMER, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Duplicate card exists in the vault for the customer\./', $result->message));
    }

    public function testCreate_withCardVerification()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(Braintree\CreditCard::PREPAID_UNKNOWN, $result->creditCardVerification->creditCard['prepaid']);
        $this->assertEquals(Braintree\CreditCard::PREPAID_RELOADABLE_UNKNOWN, $result->creditCardVerification->creditCard['prepaidReloadable']);
    }

    public function testCreate_withCardVerificationReturnsVerificationWithRiskData()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $result = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
            'options' => ['verifyCard' => true],
            'deviceData' => 'device_data'
        ]);
        $this->assertTrue($result->success);
        $this->assertNotNull($result->creditCard->verification->riskData);
        $this->assertNotNull($result->creditCard->verification->riskData->decision);
        $this->assertNotNull($result->creditCard->verification->riskData->id);
        $this->assertNotNull($result->creditCard->verification->riskData->decisionReasons);
    }

    public function testCreate_includesRiskDataWhenSkipAdvancedFraudCheckingIsFalse()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $result = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
            'options' => [
                'verifyCard' => true,
                'skipAdvancedFraudChecking' => false
            ],
        ]);
        $this->assertTrue($result->success);
        $verification = $result->creditCard->verification;
        $this->assertNotNull($verification->riskData);
    }

    public function testCreate_doesNotIncludeRiskDataWhenSkipAdvancedFraudCheckingIsTrue()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $result = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
            'options' => [
                'verifyCard' => true,
                'skipAdvancedFraudChecking' => true
            ],
        ]);
        $this->assertTrue($result->success);
        $verification = $result->creditCard->verification;
        $this->assertNull($verification->riskData);
    }

    public function testCreate_withCardVerificationAndOverriddenAmount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => ['verifyCard' => true, 'verificationAmount' => '1.02']
        ]);
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('1.02', $result->creditCardVerification->amount);
        $this->assertEquals('USD', $result->creditCardVerification->currencyIsoCode);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(Braintree\CreditCard::PREPAID_UNKNOWN, $result->creditCardVerification->creditCard['prepaid']);
        $this->assertEquals(Braintree\CreditCard::PREPAID_RELOADABLE_UNKNOWN, $result->creditCardVerification->creditCard['prepaidReloadable']);
    }

    public function testCreate_withCardVerificationAndSpecificMerchantAccount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => ['verificationMerchantAccountId' => Test\Helper::nonDefaultMerchantAccountId(), 'verifyCard' => true],
        ]);
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
    }

    public function testCreate_withBillingAddress()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Peter Tomlin',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => [
                'firstName' => 'Drew',
                'lastName' => 'Smith',
                'company' => 'Smith Co.',
                'streetAddress' => '1 E Main St',
                'extendedAddress' => 'Suite 101',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60622',
                'countryName' => 'Micronesia',
                'countryCodeAlpha2' => 'FM',
                'countryCodeAlpha3' => 'FSM',
                'countryCodeNumeric' => '583',
                'phoneNumber' => '312-123-4567'
            ]
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Peter Tomlin', $result->creditCard->cardholderName);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
        $address = $result->creditCard->billingAddress;
        $this->assertEquals('Drew', $address->firstName);
        $this->assertEquals('Smith', $address->lastName);
        $this->assertEquals('Smith Co.', $address->company);
        $this->assertEquals('1 E Main St', $address->streetAddress);
        $this->assertEquals('Suite 101', $address->extendedAddress);
        $this->assertEquals('Chicago', $address->locality);
        $this->assertEquals('IL', $address->region);
        $this->assertEquals('60622', $address->postalCode);
        $this->assertEquals('Micronesia', $address->countryName);
        $this->assertEquals('FM', $address->countryCodeAlpha2);
        $this->assertEquals('FSM', $address->countryCodeAlpha3);
        $this->assertEquals('583', $address->countryCodeNumeric);
        $this->assertEquals('312-123-4567', $address->phoneNumber);
    }

    public function testCreate_withExistingBillingAddress()
    {
        $customer = Braintree\Customer::createNoValidate();
        $existingAddress = Braintree\Address::createNoValidate([
            'customerId' => $customer->id,
            'firstName' => 'John'
        ]);
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddressId' => $existingAddress->id
        ]);
        $this->assertTrue($result->success);
        $address = $result->creditCard->billingAddress;
        $this->assertEquals($existingAddress->id, $address->id);
        $this->assertEquals('John', $address->firstName);
    }

    public function testCreate_withValidationErrors()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'expirationDate' => 'invalid',
            'billingAddress' => [
                'countryName' => 'Tuvalu',
                'countryCodeAlpha2' => 'US'
            ]
        ]);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_EXPIRATION_DATE_IS_INVALID, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Credit card number is required\./', $result->message));
        $this->assertEquals(1, preg_match('/Customer ID is required\./', $result->message));
        $this->assertEquals(1, preg_match('/Expiration date is invalid\./', $result->message));

        $errors = $result->errors->forKey('creditCard')->forKey('billingAddress')->onAttribute('base');
        $this->assertEquals(Braintree\Error\Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    public function testCreate_withAccountTypeCredit()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => Braintree\Test\CreditCardNumbers::$hiper,
            'expirationDate' => '05/12',
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'hiper_brl',
                'verificationAccountType' => 'credit'
            ]
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals('credit', $result->creditCard->verification->creditCard['accountType']);
    }

    public function testCreate_withAccountTypeDebit()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => Braintree\Test\CreditCardNumbers::$hiper,
            'expirationDate' => '05/12',
            'options' => [
                'verifyCard' => true,
                'verificationMerchantAccountId' => 'card_processor_brl',
                'verificationAccountType' => 'debit'
            ]
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals('debit', $result->creditCard->verification->creditCard['accountType']);
    }

    public function testCreate_ErrorsWithVerificationAccountTypeIsInvalid()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => Braintree\Test\CreditCardNumbers::$hiper,
            'expirationDate' => '05/12',
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
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'options' => [
                'verifyCard' => true,
                'verificationAccountType' => 'credit'
            ]
        ]);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->forKey('options')->onAttribute('verificationAccountType');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_OPTIONS_VERIFICATION_ACCOUNT_TYPE_NOT_SUPPORTED, $errors[0]->code);
    }

    public function testCreateNoValidate_throwsIfValidationsFail()
    {

        $this->expectException('Braintree\Exception\ValidationsFailed');
        $customer = Braintree\Customer::createNoValidate();
        Braintree\CreditCard::createNoValidate([
            'expirationDate' => 'invalid',
        ]);
    }

    public function testCreateNoValidate_returnsCreditCardIfValid()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCard = Braintree\CreditCard::createNoValidate([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $this->assertEquals($customer->id, $creditCard->customerId);
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Cardholder', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::sale has been deprecated in favor of Transaction::sale
    public function testSale_createsASaleUsingGivenToken()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $result = Braintree\CreditCard::sale($creditCard->token, [
            'amount' => '100.00'
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::saleNoValidate has been deprecated in favor of Transaction::saleNoValidate
    public function testSaleNoValidate_createsASaleUsingGivenToken()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree\CreditCard::saleNoValidate($creditCard->token, [
            'amount' => '100.00'
        ]);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::saleNoValidate has been deprecated in favor of Transaction::saleNoValidate
    public function testSaleNoValidate_createsASaleUsingGivenTokenAndCvv()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree\CreditCard::saleNoValidate($creditCard->token, [
            'amount' => '100.00',
            'creditCard' => [
                'cvv' => '301'
            ]
        ]);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
        $this->assertEquals('S', $transaction->cvvResponseCode);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::saleNoValidate has been deprecated in favor of Transaction::saleNoValidate
    public function testSaleNoValidate_throwsIfInvalid()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $this->expectException('Braintree\Exception\ValidationsFailed');
        Braintree\CreditCard::saleNoValidate($creditCard->token, [
            'amount' => 'invalid'
        ]);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::credit has been deprecated in favor of Transaction::credit
    public function testCredit_createsACreditUsingGivenToken()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $result = Braintree\CreditCard::credit($creditCard->token, [
            'amount' => '100.00'
        ]);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree\Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::creditNoValidate has been deprecated in favor of Transaction::creditNoValidate
    public function testCreditNoValidate_createsACreditUsingGivenToken()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree\CreditCard::creditNoValidate($creditCard->token, [
            'amount' => '100.00'
        ]);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals(Braintree\Transaction::CREDIT, $transaction->type);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    // NEXT_MAJOR_VERSION Remove this test
    // CreditCard::creditNoValidate has been deprecated in favor of Transaction::creditNoValidate
    public function testCreditNoValidate_throwsIfInvalid()
    {
        $customer = Braintree\Customer::createNoValidate([
            'creditCard' => [
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ]
        ]);
        $creditCard = $customer->creditCards[0];
        $this->expectException('Braintree\Exception\ValidationsFailed');
        Braintree\CreditCard::creditNoValidate($creditCard->token, [
            'amount' => 'invalid'
        ]);
    }

    public function testExpired()
    {
        $collection = Braintree\CreditCard::expired();
        $this->assertTrue($collection->maximumCount() > 1);

        $arr = [];
        foreach ($collection as $creditCard) {
            $this->assertTrue($creditCard->isExpired());
            array_push($arr, $creditCard->token);
        }
        $uniqueCreditCardTokens = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($uniqueCreditCardTokens));
    }


    public function testExpiringBetween()
    {
        $collection = Braintree\CreditCard::expiringBetween(
            mktime(0, 0, 0, 1, 1, 2010),
            mktime(23, 59, 59, 12, 31, 2010)
        );
        $this->assertTrue($collection->maximumCount() > 1);

        $arr = [];
        foreach ($collection as $creditCard) {
            $this->assertEquals('2010', $creditCard->expirationYear);
            array_push($arr, $creditCard->token);
        }
        $uniqueCreditCardTokens = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($uniqueCreditCardTokens));
    }

    public function testExpiringBetween_parsesCreditCardDetailsUnderTransactionsCorrectly()
    {
        $collection = Braintree\CreditCard::expiringBetween(
            mktime(0, 0, 0, 1, 1, 2010),
            mktime(23, 59, 59, 12, 31, 2010)
        );
        $this->assertTrue($collection->maximumCount() > 1);

        foreach ($collection as $creditCard) {
            foreach ($creditCard->subscriptions as $subscription) {
                foreach ($subscription->transactions as $transaction) {
                    $this->assertNotNull($transaction->creditCardDetails->expirationMonth);
                }
            }
        }
    }

    public function testFind()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $this->assertTrue($result->success);
        $creditCard = Braintree\CreditCard::find($result->creditCard->token);
        $this->assertEquals($customer->id, $creditCard->customerId);
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Cardholder', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals([], $creditCard->subscriptions);
    }

    public function testFindReturnsAssociatedSubscriptions()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => [
                'firstName' => 'Drew',
                'lastName' => 'Smith',
                'company' => 'Smith Co.',
                'streetAddress' => '1 E Main St',
                'extendedAddress' => 'Suite 101',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60622',
                'countryName' => 'United States of America'
            ]
        ]);
        $id = strval(rand());
        Braintree\Subscription::create([
            'id' => $id,
            'paymentMethodToken' => $result->creditCard->token,
            'planId' => 'integration_trialless_plan',
            'price' => '1.00'
        ]);
        $creditCard = Braintree\CreditCard::find($result->creditCard->token);
        $this->assertEquals($id, $creditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $creditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $creditCard->subscriptions[0]->price);
    }

    public function testFind_throwsIfCannotBeFound()
    {
        $this->expectException('Braintree\Exception\NotFound');
        Braintree\CreditCard::find('invalid-token');
    }

    public function testFind_throwsUsefulErrorMessagesWhenEmpty()
    {
        $this->expectException('InvalidArgumentException', 'expected credit card id to be set');
        Braintree\CreditCard::find('');
    }

    public function testFind_throwsUsefulErrorMessagesWhenInvalid()
    {
        $this->expectException('InvalidArgumentException', '@ is an invalid credit card token');
        Braintree\CreditCard::find('@');
    }

    public function testFromNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            "credit_card" => [
                "number" => "4009348888881881",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ],
            "customerId" => $customer->id
        ]);

        $creditCard = Braintree\CreditCard::fromNonce($nonce);

        $customer = Braintree\Customer::find($customer->id);
        $this->assertEquals($customer->creditCards[0], $creditCard);
    }

    public function testFromNonce_ReturnsErrorWhenNoncePointsToSharedCard()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            "credit_card" => [
                "number" => "4009348888881881",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ],
            "share" => true
        ]);

        $this->expectException('Braintree\Exception\NotFound', "not found");
        Braintree\CreditCard::fromNonce($nonce);
    }

    public function testCreate_fromThreeDSecureNonceWithInvalidPassThruParams()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$transactable;

        $result = Braintree\CreditCard::create([
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

        $result = Braintree\CreditCard::create([
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

        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'verifyCard' => 'true',
            ]
        ]);

        $threeDSecureInfo = $result->creditCard->verification->threeDSecureInfo;
        $this->assertEquals("authenticate_successful", $threeDSecureInfo->status);
        $this->assertIsBool($threeDSecureInfo->liabilityShiftPossible);
        $this->assertIsBool($threeDSecureInfo->liabilityShifted);
        $this->assertIsString($threeDSecureInfo->enrolled);
        $this->assertIsString($threeDSecureInfo->xid);
        $this->assertIsString($threeDSecureInfo->cavv);
        $this->assertIsString($threeDSecureInfo->eciFlag);
        $this->assertIsString($threeDSecureInfo->threeDSecureVersion);
    }

    public function testFromNonce_ReturnsErrorWhenNonceIsConsumed()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = $http->nonce_for_new_card([
            "credit_card" => [
                "number" => "4009348888881881",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ],
            "customerId" => $customer->id
        ]);

        Braintree\CreditCard::fromNonce($nonce);
        $this->expectException('Braintree\Exception\NotFound', "consumed");
        Braintree\CreditCard::fromNonce($nonce);
    }

    public function testUpdate()
    {
        $customer = Braintree\Customer::createNoValidate();
        $createResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $this->assertTrue($createResult->success);
        $updateResult = Braintree\CreditCard::update($createResult->creditCard->token, [
            'cardholderName' => 'New Cardholder',
            'number' => '4111111111111111',
            'expirationDate' => '07/14'
        ]);
        $this->assertEquals($customer->id, $updateResult->creditCard->customerId);
        $this->assertEquals('411111', $updateResult->creditCard->bin);
        $this->assertEquals('1111', $updateResult->creditCard->last4);
        $this->assertEquals('New Cardholder', $updateResult->creditCard->cardholderName);
        $this->assertEquals('07/2014', $updateResult->creditCard->expirationDate);
    }

    public function testUpdate_withCardVerification()
    {
        $customer = Braintree\Customer::createNoValidate();
        $initialCreditCard = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $result = Braintree\CreditCard::update($initialCreditCard->token, [
            'billingAddress' => [
                'region' => 'IL'
            ],
            'options' => ['verifyCard' => true]
        ]);
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(Test\Helper::defaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    public function testUpdate_fromThreeDSecureNonceWithInvalidPassThruParams()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $customer = Braintree\Customer::createNoValidate();
        $initialCreditCard = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $result = Braintree\CreditCard::update($initialCreditCard->token, [
            'billingAddress' => [
                'region' => 'IL'
            ],
            'paymentMethodNonce' =>  Braintree\Test\Nonces::$transactable,
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
            'options' => ['verifyCard' => true]
        ]);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('verification')->onAttribute('threeDSecureVersion');
        $this->assertEquals(Braintree\Error\Codes::VERIFICATION_THREE_D_SECURE_THREE_D_SECURE_VERSION_IS_INVALID, $errors[0]->code);
        $this->assertEquals(1, preg_match('/The version of 3D Secure authentication must be composed only of digits and separated by periods/', $result->message));
    }

    public function testUpdate_fromThreeDSecureNonceWithPassThruParams()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $customer = Braintree\Customer::createNoValidate();
        $initialCreditCard = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $result = Braintree\CreditCard::update($initialCreditCard->token, [
            'billingAddress' => [
                'region' => 'IL'
            ],
            'paymentMethodNonce' =>  Braintree\Test\Nonces::$transactable,
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
            'options' => ['verifyCard' => true]
        ]);


        $this->assertTrue($result->success);
    }
    public function testUpdate_withCardVerificationAndSpecificMerchantAccount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $initialCreditCard = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $result = Braintree\CreditCard::update($initialCreditCard->token, [
            'billingAddress' => [
                'region' => 'IL'
            ],
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::nonDefaultMerchantAccountId(),
                'verifyCard' => true
            ]
        ]);
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals(Test\Helper::nonDefaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    public function testUpdate_createsNewBillingAddressByDefault()
    {
        $customer = Braintree\Customer::createNoValidate();
        $initialCreditCard = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => [
                'streetAddress' => '123 Nigeria Ave'
            ]
        ])->creditCard;

        $updatedCreditCard = Braintree\CreditCard::update($initialCreditCard->token, [
            'billingAddress' => [
                'region' => 'IL'
            ]
        ])->creditCard;
        $this->assertEquals('IL', $updatedCreditCard->billingAddress->region);
        $this->assertNull($updatedCreditCard->billingAddress->streetAddress);
        $this->assertNotEquals($initialCreditCard->billingAddress->id, $updatedCreditCard->billingAddress->id);
    }

    public function testUpdate_updatesExistingBillingAddressIfUpdateExistingOptionIsTrue()
    {
        $customer = Braintree\Customer::createNoValidate();
        $initialCreditCard = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => [
                'countryName' => 'Turkey',
                'countryCodeAlpha2' => 'TR',
                'countryCodeAlpha3' => 'TUR',
                'countryCodeNumeric' => '792',
            ]
        ])->creditCard;

        $updatedCreditCard = Braintree\CreditCard::update($initialCreditCard->token, [
            'billingAddress' => [
                'countryName' => 'Thailand',
                'countryCodeAlpha2' => 'TH',
                'countryCodeAlpha3' => 'THA',
                'countryCodeNumeric' => '764',
                'options' => [
                    'updateExisting' => true
                ]
            ]
        ])->creditCard;
        $this->assertEquals('Thailand', $updatedCreditCard->billingAddress->countryName);
        $this->assertEquals('TH', $updatedCreditCard->billingAddress->countryCodeAlpha2);
        $this->assertEquals('THA', $updatedCreditCard->billingAddress->countryCodeAlpha3);
        $this->assertEquals('764', $updatedCreditCard->billingAddress->countryCodeNumeric);
        $this->assertEquals($initialCreditCard->billingAddress->id, $updatedCreditCard->billingAddress->id);
    }

    public function testUpdate_includesRiskDataWhenSkipAdvancedFraudCheckingIsFalse()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $initialCreditCard = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
        ])->creditCard;
        $result = $gateway->creditCard()->update($initialCreditCard->token, [
            'expirationDate' => '06/2023',
            'options' => ['verifyCard' => true, 'skipAdvancedFraudChecking' => false],
        ]);

        $this->assertTrue($result->success);
        $verification = $result->creditCard->verification;
        $this->assertNotNull($verification->riskData);
    }

    public function testUpdate_doesNotIncludeRiskDataWhenSkipAdvancedFraudCheckingIsTrue()
    {
        $gateway = Test\Helper::fraudProtectionEnterpriseIntegrationMerchantGateway();
        $customer = $gateway->customer()->createNoValidate();
        $initialCreditCard = $gateway->creditCard()->create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
        ])->creditCard;
        $result = $gateway->creditCard()->update($initialCreditCard->token, [
            'expirationDate' => '06/2023',
            'options' => ['verifyCard' => true, 'skipAdvancedFraudChecking' => true],
        ]);

        $this->assertTrue($result->success);
        $verification = $result->creditCard->verification;
        $this->assertNull($verification->riskData);
    }

    public function testUpdate_canChangeToken()
    {
        $oldToken = strval(rand());
        $newToken = strval(rand());

        $customer = Braintree\Customer::createNoValidate();
        $createResult = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'token' => $oldToken,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $this->assertTrue($createResult->success);
        $updateResult = Braintree\CreditCard::update($oldToken, [
            'token' => $newToken
        ]);
        $this->assertEquals($customer->id, $updateResult->creditCard->customerId);
        $this->assertEquals($newToken, $updateResult->creditCard->token);
        $this->assertEquals($newToken, Braintree\CreditCard::find($newToken)->token);
    }

    public function testUpdateNoValidate()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCard = Braintree\CreditCard::createNoValidate([
            'customerId' => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $updatedCard = Braintree\CreditCard::updateNoValidate($creditCard->token, [
            'cardholderName' => 'New Cardholder',
            'number' => '4111111111111111',
            'expirationDate' => '07/14'
        ]);
        $this->assertEquals($customer->id, $updatedCard->customerId);
        $this->assertEquals('411111', $updatedCard->bin);
        $this->assertEquals('1111', $updatedCard->last4);
        $this->assertEquals('New Cardholder', $updatedCard->cardholderName);
        $this->assertEquals('07/2014', $updatedCard->expirationDate);
    }

    public function testUpdateNoValidate_throwsIfInvalid()
    {
        $customer = Braintree\Customer::createNoValidate();
        $creditCard = Braintree\CreditCard::createNoValidate([
            'customerId' => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        $this->expectException('Braintree\Exception\ValidationsFailed');
        Braintree\CreditCard::updateNoValidate($creditCard->token, [
            'number' => 'invalid',
        ]);
    }

    public function testUpdate_withDefault()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card1 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;
        $card2 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $this->assertTrue($card1->isDefault());
        $this->assertFalse($card2->isDefault());

        Braintree\CreditCard::update($card2->token, [
            'options' => ['makeDefault' => true]
        ])->creditCard;

        $this->assertFalse(Braintree\CreditCard::find($card1->token)->isDefault());
        $this->assertTrue(Braintree\CreditCard::find($card2->token)->isDefault());
    }

    public function testUpdate_withDuplicateCardCheckForCustomer()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card1 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;
        $card2 = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '4111111111111111',
            'expirationDate' => '05/12'
        ])->creditCard;

        $result = Braintree\CreditCard::update($card2->token, [
            'number' => '5105105105105100',
            'options' => ['failOnDuplicatePaymentMethodForCustomer' => true]
        ]);

        $errors = $result->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Braintree\Error\Codes::CREDIT_CARD_DUPLICATE_CARD_EXISTS_FOR_CUSTOMER, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Duplicate card exists in the vault for the customer\./', $result->message));
    }

    public function testDelete_deletesThePaymentMethod()
    {
        $customer = Braintree\Customer::createNoValidate([]);
        $creditCard = Braintree\CreditCard::createNoValidate([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ]);
        Braintree\CreditCard::find($creditCard->token);
        Braintree\CreditCard::delete($creditCard->token);
        $this->expectException('Braintree\Exception\NotFound');
        Braintree\CreditCard::find($creditCard->token);
    }

    public function testGatewayRejectionOnCVV()
    {
        $old_merchant_id = Braintree\Configuration::merchantId();
        $old_public_key = Braintree\Configuration::publicKey();
        $old_private_key = Braintree\Configuration::privateKey();

        Braintree\Configuration::merchantId('processing_rules_merchant_id');
        Braintree\Configuration::publicKey('processing_rules_public_key');
        Braintree\Configuration::privateKey('processing_rules_private_key');

        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
            'cvv' => '200',
            'options' => ['verifyCard' => true]
        ]);

        Braintree\Configuration::merchantId($old_merchant_id);
        Braintree\Configuration::publicKey($old_public_key);
        Braintree\Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Transaction::CVV, $result->creditCardVerification->gatewayRejectionReason);
    }

    public function testGatewayRejectionIsNullOnProcessorDecline()
    {
        $old_merchant_id = Braintree\Configuration::merchantId();
        $old_public_key = Braintree\Configuration::publicKey();
        $old_private_key = Braintree\Configuration::privateKey();

        Braintree\Configuration::merchantId('processing_rules_merchant_id');
        Braintree\Configuration::publicKey('processing_rules_public_key');
        Braintree\Configuration::privateKey('processing_rules_private_key');

        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'cvv' => '200',
            'options' => ['verifyCard' => true]
        ]);

        Braintree\Configuration::merchantId($old_merchant_id);
        Braintree\Configuration::publicKey($old_public_key);
        Braintree\Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $this->assertNull($result->creditCardVerification->gatewayRejectionReason);
    }

    public function testPrepaidCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::PREPAID,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::PREPAID_YES, $result->creditCard->prepaid);
    }

    public function testPrepaidReloadableCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::PREPAID_RELOADABLE,
            'expirationDate' => '05/25',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::PREPAID_RELOADABLE_YES, $result->creditCard->prepaidReloadable);
    }

    public function testCommercialCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::COMMERCIAL,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::COMMERCIAL_YES, $result->creditCard->commercial);
    }

    public function testDebitCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::DEBIT,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::DEBIT_YES, $result->creditCard->debit);
    }

    public function testPayrollCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::PAYROLL,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::PAYROLL_YES, $result->creditCard->payroll);
        $this->assertEquals('MSA', $result->creditCard->productId);
    }

    public function testHealthCareCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::HEALTHCARE,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::HEALTHCARE_YES, $result->creditCard->healthcare);
        $this->assertEquals('J3', $result->creditCard->productId);
    }

    public function testDurbinRegulatedCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::DURBIN_REGULATED,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::DURBIN_REGULATED_YES, $result->creditCard->durbinRegulated);
    }

    public function testCountryOfIssuanceCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::COUNTRY_OF_ISSUANCE,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals("USA", $result->creditCard->countryOfIssuance);
    }

    public function testIssuingBankCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::ISSUING_BANK,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals("NETWORK ONLY", $result->creditCard->issuingBank);
    }

    public function testNegativeCardTypeIndicators()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::NO,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::PREPAID_NO, $result->creditCard->prepaid);
        $this->assertEquals(Braintree\CreditCard::PREPAID_RELOADABLE_NO, $result->creditCard->prepaidReloadable);
        $this->assertEquals(Braintree\CreditCard::DURBIN_REGULATED_NO, $result->creditCard->durbinRegulated);
        $this->assertEquals(Braintree\CreditCard::PAYROLL_NO, $result->creditCard->payroll);
        $this->assertEquals(Braintree\CreditCard::DEBIT_NO, $result->creditCard->debit);
        $this->assertEquals(Braintree\CreditCard::HEALTHCARE_NO, $result->creditCard->healthcare);
        $this->assertEquals(Braintree\CreditCard::COMMERCIAL_NO, $result->creditCard->commercial);
        $this->assertEquals('MSB', $result->creditCard->productId);
    }

    public function testUnknownCardTypeIndicators()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => CardTypeIndicators::UNKNOWN,
            'expirationDate' => '05/12',
            'options' => ['verifyCard' => true]
        ]);
        $this->assertEquals(Braintree\CreditCard::PREPAID_UNKNOWN, $result->creditCard->prepaid);
        $this->assertEquals(Braintree\CreditCard::PREPAID_RELOADABLE_UNKNOWN, $result->creditCard->prepaidReloadable);
        $this->assertEquals(Braintree\CreditCard::DURBIN_REGULATED_UNKNOWN, $result->creditCard->durbinRegulated);
        $this->assertEquals(Braintree\CreditCard::PAYROLL_UNKNOWN, $result->creditCard->payroll);
        $this->assertEquals(Braintree\CreditCard::DEBIT_UNKNOWN, $result->creditCard->debit);
        $this->assertEquals(Braintree\CreditCard::HEALTHCARE_UNKNOWN, $result->creditCard->healthcare);
        $this->assertEquals(Braintree\CreditCard::COMMERCIAL_UNKNOWN, $result->creditCard->commercial);
        $this->assertEquals(Braintree\CreditCard::COUNTRY_OF_ISSUANCE_UNKNOWN, $result->creditCard->countryOfIssuance);
        $this->assertEquals(Braintree\CreditCard::ISSUING_BANK_UNKNOWN, $result->creditCard->issuingBank);
        $this->assertEquals(Braintree\CreditCard::PRODUCT_ID_UNKNOWN, $result->creditCard->productId);
    }

    public function testFindNetworkTokenizedCreditCard()
    {
        $card = Braintree\CreditCard::find('network_tokenized_credit_card');
        $this->assertTrue($card->isNetworkTokenized);
    }

    public function testFindNonNetworkTokenizedCreditCard()
    {
        $customer = Braintree\Customer::createNoValidate();
        $card = Braintree\CreditCard::create([
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ])->creditCard;

        $saved_card = Braintree\CreditCard::find($card->token);
        $this->assertFalse($saved_card->isNetworkTokenized);
    }
}
