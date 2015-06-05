<?php namespace Braintree\Tests\Integration;

use Braintree\Address;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\Result\CreditCardVerification;
use Braintree\Subscription;
use Braintree\Test\VenmoSdk;
use Braintree\Tests\Braintree\CreditCardNumbers\CardTypeIndicators;
use Braintree\Tests\TestHelper;
use Braintree\Transaction;
use Braintree\TransparentRedirect;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class CreditCardTest extends \PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
        $this->assertEquals(1, preg_match('/\A\w{32}\z/', $result->creditCard->uniqueNumberIdentifier));
        $this->assertFalse($result->creditCard->isVenmoSdk());
        $this->assertEquals(1, preg_match('/png/', $result->creditCard->imageUrl));
    }

    function testGatewayCreate()
    {
        $customer = Customer::createNoValidate();

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $result = $gateway->creditCard()->create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));

        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
    }

    function testCreate_withDefault()
    {
        $customer = Customer::createNoValidate();
        $card1 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;
        $this->assertTrue($card1->isDefault());

        $card2 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'options'        => array(
                'makeDefault' => true
            )
        ))->creditCard;

        $card1 = CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    function testAddCardToExistingCustomerUsingNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "credit_card" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "share"       => true
        ));

        $result = CreditCard::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame("411111", $result->creditCard->bin);
        $this->assertSame("1111", $result->creditCard->last4);
    }

    function testCreate_withSecurityParams()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'      => $customer->id,
            'deviceSessionId' => 'abc_123',
            'fraudMerchantId' => '456',
            'cardholderName'  => 'Cardholder',
            'number'          => '5105105105105100',
            'expirationDate'  => '05/12'
        ));

        $this->assertTrue($result->success);
    }

    function testCreate_withExpirationMonthAndYear()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'      => $customer->id,
            'cardholderName'  => 'Cardholder',
            'number'          => '5105105105105100',
            'expirationMonth' => '05',
            'expirationYear'  => '2011'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($customer->id, $result->creditCard->customerId);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('Cardholder', $result->creditCard->cardholderName);
        $this->assertEquals('05/2011', $result->creditCard->expirationDate);
    }

    function testCreate_withSpecifyingToken()
    {
        $token = strval(rand());
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'token'          => $token
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($token, $result->creditCard->token);
        $this->assertEquals($token, CreditCard::find($token)->token);
    }

    function testCreate_withDuplicateCardCheck()
    {
        $customer = Customer::createNoValidate();

        $attributes = array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'options'        => array('failOnDuplicatePaymentMethod' => true)
        );
        CreditCard::create($attributes);

        $result = CreditCard::create($attributes);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Error_Codes::CREDIT_CARD_DUPLICATE_CARD_EXISTS, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Duplicate card exists in the vault\./', $result->message));
    }

    function testCreate_withCardVerification()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(CreditCard::PREPAID_UNKNOWN, $result->creditCardVerification->creditCard["prepaid"]);
    }

    function testCreate_withCardVerificationReturnsVerificationWithRiskData()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '4111111111111111',
            'expirationDate' => '05/2011',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertTrue($result->success);
        $this->assertNotNull($result->creditCard->verification->riskData);
        $this->assertNotNull($result->creditCard->verification->riskData->decision);
    }

    function testCreate_withCardVerificationAndOverriddenAmount()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'options'        => array('verifyCard' => true, 'verificationAmount' => '1.02')
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(CreditCard::PREPAID_UNKNOWN, $result->creditCardVerification->creditCard["prepaid"]);
    }

    function testCreate_withCardVerificationAndSpecificMerchantAccount()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'options'        => array(
                'verificationMerchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
                'verifyCard'                    => true
            )
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
    }

    function testCreate_withBillingAddress()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Peter Tomlin',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'firstName'          => 'Drew',
                'lastName'           => 'Smith',
                'company'            => 'Smith Co.',
                'streetAddress'      => '1 E Main St',
                'extendedAddress'    => 'Suite 101',
                'locality'           => 'Chicago',
                'region'             => 'IL',
                'postalCode'         => '60622',
                'countryName'        => 'Micronesia',
                'countryCodeAlpha2'  => 'FM',
                'countryCodeAlpha3'  => 'FSM',
                'countryCodeNumeric' => '583'
            )
        ));
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
    }

    function testCreate_withExistingBillingAddress()
    {
        $customer = Customer::createNoValidate();
        $existingAddress = Address::createNoValidate(array(
            'customerId' => $customer->id,
            'firstName'  => 'John'
        ));
        $result = CreditCard::create(array(
            'customerId'       => $customer->id,
            'number'           => '5105105105105100',
            'expirationDate'   => '05/12',
            'billingAddressId' => $existingAddress->id
        ));
        $this->assertTrue($result->success);
        $address = $result->creditCard->billingAddress;
        $this->assertEquals($existingAddress->id, $address->id);
        $this->assertEquals('John', $address->firstName);
    }

    function testCreate_withValidationErrors()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'expirationDate' => 'invalid',
            'billingAddress' => array(
                'countryName'       => 'Tuvalu',
                'countryCodeAlpha2' => 'US'
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Error_Codes::CREDIT_CARD_EXPIRATION_DATE_IS_INVALID, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Credit card number is required\./', $result->message));
        $this->assertEquals(1, preg_match('/Customer ID is required\./', $result->message));
        $this->assertEquals(1, preg_match('/Expiration date is invalid\./', $result->message));

        $errors = $result->errors->forKey('creditCard')->forKey('billingAddress')->onAttribute('base');
        $this->assertEquals(Error_Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    function testCreate_withVenmoSdkPaymentMethodCode()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'                => $customer->id,
            'venmoSdkPaymentMethodCode' => VenmoSdk::generateTestPaymentMethodCode("378734493671000")
        ));
        $this->assertTrue($result->success);
        $this->assertEquals("378734", $result->creditCard->bin);
    }

    function testCreate_with_invalid_venmoSdkPaymentMethodCode()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'                => $customer->id,
            'venmoSdkPaymentMethodCode' => VenmoSdk::getInvalidPaymentMethodCode()
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('venmoSdkPaymentMethodCode');
        $this->assertEquals($errors[0]->code, Error_Codes::CREDIT_CARD_INVALID_VENMO_SDK_PAYMENT_METHOD_CODE);

    }

    function testCreate_with_venmoSdkSession()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'options'        => array(
                'venmoSdkSession' => VenmoSdk::getTestSession()
            )
        ));
        $this->assertTrue($result->success);
        $this->assertTrue($result->creditCard->isVenmoSdk());
    }

    function testCreate_with_invalidVenmoSdkSession()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'options'        => array(
                'venmoSdkSession' => VenmoSdk::getInvalidTestSession()
            )
        ));
        $this->assertTrue($result->success);
        $this->assertFalse($result->creditCard->isVenmoSdk());
    }

    function testCreateNoValidate_throwsIfValidationsFail()
    {

        $this->setExpectedException('\Braintree\Exception\ValidationsFailed');
        $customer = Customer::createNoValidate();
        CreditCard::createNoValidate(array(
            'expirationDate' => 'invalid',
        ));
    }

    function testCreateNoValidate_returnsCreditCardIfValid()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertEquals($customer->id, $creditCard->customerId);
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Cardholder', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
    }

    function testCreateFromTransparentRedirect()
    {
        TestHelper::suppressDeprecationWarnings();
        $customer = Customer::createNoValidate();
        $queryString = $this->createCreditCardViaTr(
            array(
                'credit_card' => array(
                    'number'          => '5105105105105100',
                    'expiration_date' => '05/12'
                )
            ),
            array(
                'creditCard' => array(
                    'customerId' => $customer->id
                )
            )
        );
        $result = CreditCard::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
    }

    function testCreateFromTransparentRedirect_withDefault()
    {
        TestHelper::suppressDeprecationWarnings();
        $customer = Customer::createNoValidate();
        $queryString = $this->createCreditCardViaTr(
            array(
                'credit_card' => array(
                    'number'          => '5105105105105100',
                    'expiration_date' => '05/12',
                    'options'         => array('make_default' => true)
                )
            ),
            array(
                'creditCard' => array(
                    'customerId' => $customer->id
                )
            )
        );
        $result = CreditCard::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->creditCard->isDefault());
    }

    function testUpdateFromTransparentRedirect()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $queryString = $this->updateCreditCardViaTr(
            array(
                'credit_card' => array(
                    'number'          => '4111111111111111',
                    'expiration_date' => '01/11'
                )
            ),
            array('paymentMethodToken' => $creditCard->token)
        );
        $result = CreditCard::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('411111', $result->creditCard->bin);
        $this->assertEquals('1111', $result->creditCard->last4);
        $this->assertEquals('01/2011', $result->creditCard->expirationDate);
    }

    function testUpdateFromTransparentRedirect_withDefault()
    {
        $customer = Customer::createNoValidate();
        $card1 = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $card2 = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertFalse($card2->isDefault());

        $queryString = $this->updateCreditCardViaTr(
            array(
                'credit_card' => array(
                    'options' => array(
                        'make_default' => true
                    )
                )
            ),
            array('paymentMethodToken' => $card2->token)
        );
        $result = CreditCard::updateFromTransparentRedirect($queryString);
        $this->assertFalse(CreditCard::find($card1->token)->isDefault());
        $this->assertTrue(CreditCard::find($card2->token)->isDefault());
    }

    function testUpdateFromTransparentRedirect_andUpdateExistingBillingAddress()
    {
        $customer = Customer::createNoValidate();
        $card = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'firstName'       => 'Drew',
                'lastName'        => 'Smith',
                'company'         => 'Smith Co.',
                'streetAddress'   => '123 Old St',
                'extendedAddress' => 'Suite 101',
                'locality'        => 'Chicago',
                'region'          => 'IL',
                'postalCode'      => '60622',
                'countryName'     => 'United States of America'
            )
        ));

        $queryString = $this->updateCreditCardViaTr(
            array(),
            array(
                'paymentMethodToken' => $card->token,
                'creditCard'         => array(
                    'billingAddress' => array(
                        'streetAddress' => '123 New St',
                        'locality'      => 'St. Louis',
                        'region'        => 'MO',
                        'postalCode'    => '63119',
                        'options'       => array(
                            'updateExisting' => true
                        )
                    )
                )
            )
        );
        $result = CreditCard::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $card = $result->creditCard;
        $this->assertEquals(1, sizeof(Customer::find($customer->id)->addresses));
        $this->assertEquals('123 New St', $card->billingAddress->streetAddress);
        $this->assertEquals('St. Louis', $card->billingAddress->locality);
        $this->assertEquals('MO', $card->billingAddress->region);
        $this->assertEquals('63119', $card->billingAddress->postalCode);
    }

    function testSale_createsASaleUsingGivenToken()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $result = CreditCard::sale($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    function testSaleNoValidate_createsASaleUsingGivenToken()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = CreditCard::saleNoValidate($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    function testSaleNoValidate_createsASaleUsingGivenTokenAndCvv()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = CreditCard::saleNoValidate($creditCard->token, array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cvv' => '301'
            )
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
        $this->assertEquals('S', $transaction->cvvResponseCode);
    }

    function testSaleNoValidate_throwsIfInvalid()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $this->setExpectedException('\Braintree\Exception\ValidationsFailed');
        CreditCard::saleNoValidate($creditCard->token, array(
            'amount' => 'invalid'
        ));
    }

    function testCredit_createsACreditUsingGivenToken()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $result = CreditCard::credit($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    function testCreditNoValidate_createsACreditUsingGivenToken()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = CreditCard::creditNoValidate($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals(Transaction::CREDIT, $transaction->type);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    function testCreditNoValidate_throwsIfInvalid()
    {
        $customer = Customer::createNoValidate(array(
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $this->setExpectedException('\Braintree\Exception\ValidationsFailed');
        CreditCard::creditNoValidate($creditCard->token, array(
            'amount' => 'invalid'
        ));
    }

    function testExpired()
    {
        $collection = CreditCard::expired();
        $this->assertTrue($collection->maximumCount() > 1);

        $arr = array();
        foreach ($collection as $creditCard) {
            $this->assertTrue($creditCard->isExpired());
            array_push($arr, $creditCard->token);
        }
        $uniqueCreditCardTokens = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($uniqueCreditCardTokens));
    }


    function testExpiringBetween()
    {
        $collection = CreditCard::expiringBetween(
            mktime(0, 0, 0, 1, 1, 2010),
            mktime(23, 59, 59, 12, 31, 2010)
        );
        $this->assertTrue($collection->maximumCount() > 1);

        $arr = array();
        foreach ($collection as $creditCard) {
            $this->assertEquals('2010', $creditCard->expirationYear);
            array_push($arr, $creditCard->token);
        }
        $uniqueCreditCardTokens = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($uniqueCreditCardTokens));
    }

    function testExpiringBetween_parsesCreditCardDetailsUnderTransactionsCorrectly()
    {
        $collection = CreditCard::expiringBetween(
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

    function testFind()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($result->success);
        $creditCard = CreditCard::find($result->creditCard->token);
        $this->assertEquals($customer->id, $creditCard->customerId);
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Cardholder', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals(array(), $creditCard->subscriptions);
    }

    function testFindReturnsAssociatedSubscriptions()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'firstName'       => 'Drew',
                'lastName'        => 'Smith',
                'company'         => 'Smith Co.',
                'streetAddress'   => '1 E Main St',
                'extendedAddress' => 'Suite 101',
                'locality'        => 'Chicago',
                'region'          => 'IL',
                'postalCode'      => '60622',
                'countryName'     => 'United States of America'
            )
        ));
        $id = strval(rand());
        Subscription::create(array(
            'id'                 => $id,
            'paymentMethodToken' => $result->creditCard->token,
            'planId'             => 'integration_trialless_plan',
            'price'              => '1.00'
        ));
        $creditCard = CreditCard::find($result->creditCard->token);
        $this->assertEquals($id, $creditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $creditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $creditCard->subscriptions[0]->price);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('\Braintree\Exception\NotFound');
        CreditCard::find('invalid-token');
    }

    function testFind_throwsUsefulErrorMessagesWhenEmpty()
    {
        $this->setExpectedException('\InvalidArgumentException', 'expected credit card id to be set');
        CreditCard::find('');
    }

    function testFind_throwsUsefulErrorMessagesWhenInvalid()
    {
        $this->setExpectedException('\InvalidArgumentException', '@ is an invalid credit card token');
        CreditCard::find('@');
    }

    function testFromNonce()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "credit_card" => array(
                "number"          => "4009348888881881",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "customerId"  => $customer->id
        ));

        $creditCard = CreditCard::fromNonce($nonce);

        $customer = Customer::find($customer->id);
        $this->assertEquals($customer->creditCards[0], $creditCard);
    }

    function testFromNonce_ReturnsErrorWhenNoncePointsToSharedCard()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "credit_card" => array(
                "number"          => "4009348888881881",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "share"       => true
        ));

        $this->setExpectedException('\Braintree\Exception\NotFound', "not found");
        CreditCard::fromNonce($nonce);
    }

    function testFromNonce_ReturnsErrorWhenNonceIsConsumed()
    {
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "credit_card" => array(
                "number"          => "4009348888881881",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "customerId"  => $customer->id
        ));

        CreditCard::fromNonce($nonce);
        $this->setExpectedException('\Braintree\Exception\NotFound', "consumed");
        CreditCard::fromNonce($nonce);
    }

    function testUpdate()
    {
        $customer = Customer::createNoValidate();
        $createResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($createResult->success);
        $updateResult = CreditCard::update($createResult->creditCard->token, array(
            'cardholderName' => 'New Cardholder',
            'number'         => '4111111111111111',
            'expirationDate' => '07/14'
        ));
        $this->assertEquals($customer->id, $updateResult->creditCard->customerId);
        $this->assertEquals('411111', $updateResult->creditCard->bin);
        $this->assertEquals('1111', $updateResult->creditCard->last4);
        $this->assertEquals('New Cardholder', $updateResult->creditCard->cardholderName);
        $this->assertEquals('07/2014', $updateResult->creditCard->expirationDate);
    }

    function testUpdate_withCardVerification()
    {
        $customer = Customer::createNoValidate();
        $initialCreditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $result = CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            ),
            'options'        => array('verifyCard' => true)
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(TestHelper::defaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    function testUpdate_withCardVerificationAndSpecificMerchantAccount()
    {
        $customer = Customer::createNoValidate();
        $initialCreditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $result = CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            ),
            'options'        => array(
                'verificationMerchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
                'verifyCard'                    => true
            )
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals(TestHelper::nonDefaultMerchantAccountId(),
            $result->creditCardVerification->merchantAccountId);
    }

    function testUpdate_createsNewBillingAddressByDefault()
    {
        $customer = Customer::createNoValidate();
        $initialCreditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ))->creditCard;

        $updatedCreditCard = CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            )
        ))->creditCard;
        $this->assertEquals('IL', $updatedCreditCard->billingAddress->region);
        $this->assertNull($updatedCreditCard->billingAddress->streetAddress);
        $this->assertNotEquals($initialCreditCard->billingAddress->id, $updatedCreditCard->billingAddress->id);
    }

    function testUpdate_updatesExistingBillingAddressIfUpdateExistingOptionIsTrue()
    {
        $customer = Customer::createNoValidate();
        $initialCreditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'countryName'        => 'Turkey',
                'countryCodeAlpha2'  => 'TR',
                'countryCodeAlpha3'  => 'TUR',
                'countryCodeNumeric' => '792',
            )
        ))->creditCard;

        $updatedCreditCard = CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'countryName'        => 'Thailand',
                'countryCodeAlpha2'  => 'TH',
                'countryCodeAlpha3'  => 'THA',
                'countryCodeNumeric' => '764',
                'options'            => array(
                    'updateExisting' => true
                )
            )
        ))->creditCard;
        $this->assertEquals('Thailand', $updatedCreditCard->billingAddress->countryName);
        $this->assertEquals('TH', $updatedCreditCard->billingAddress->countryCodeAlpha2);
        $this->assertEquals('THA', $updatedCreditCard->billingAddress->countryCodeAlpha3);
        $this->assertEquals('764', $updatedCreditCard->billingAddress->countryCodeNumeric);
        $this->assertEquals($initialCreditCard->billingAddress->id, $updatedCreditCard->billingAddress->id);
    }

    function testUpdate_canChangeToken()
    {
        $oldToken = strval(rand());
        $newToken = strval(rand());

        $customer = Customer::createNoValidate();
        $createResult = CreditCard::create(array(
            'customerId'     => $customer->id,
            'token'          => $oldToken,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($createResult->success);
        $updateResult = CreditCard::update($oldToken, array(
            'token' => $newToken
        ));
        $this->assertEquals($customer->id, $updateResult->creditCard->customerId);
        $this->assertEquals($newToken, $updateResult->creditCard->token);
        $this->assertEquals($newToken, CreditCard::find($newToken)->token);
    }

    function testUpdateNoValidate()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $updatedCard = CreditCard::updateNoValidate($creditCard->token, array(
            'cardholderName' => 'New Cardholder',
            'number'         => '4111111111111111',
            'expirationDate' => '07/14'
        ));
        $this->assertEquals($customer->id, $updatedCard->customerId);
        $this->assertEquals('411111', $updatedCard->bin);
        $this->assertEquals('1111', $updatedCard->last4);
        $this->assertEquals('New Cardholder', $updatedCard->cardholderName);
        $this->assertEquals('07/2014', $updatedCard->expirationDate);
    }

    function testUpdateNoValidate_throwsIfInvalid()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->setExpectedException('\Braintree\Exception\ValidationsFailed');
        CreditCard::updateNoValidate($creditCard->token, array(
            'number' => 'invalid',
        ));
    }

    function testUpdate_withDefault()
    {
        $customer = Customer::createNoValidate();
        $card1 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;
        $card2 = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $this->assertTrue($card1->isDefault());
        $this->assertFalse($card2->isDefault());

        CreditCard::update($card2->token, array(
            'options' => array('makeDefault' => true)
        ))->creditCard;

        $this->assertFalse(CreditCard::find($card1->token)->isDefault());
        $this->assertTrue(CreditCard::find($card2->token)->isDefault());
    }

    function testDelete_deletesThePaymentMethod()
    {
        $customer = Customer::createNoValidate(array());
        $creditCard = CreditCard::createNoValidate(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        CreditCard::find($creditCard->token);
        CreditCard::delete($creditCard->token);
        $this->setExpectedException('\Braintree\Exception\NotFound');
        CreditCard::find($creditCard->token);
    }

    function testGatewayRejectionOnCVV()
    {
        $old_merchant_id = Configuration::merchantId();
        $old_public_key = Configuration::publicKey();
        $old_private_key = Configuration::privateKey();

        Configuration::merchantId('processing_rules_merchant_id');
        Configuration::publicKey('processing_rules_public_key');
        Configuration::privateKey('processing_rules_private_key');

        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '4111111111111111',
            'expirationDate' => '05/2011',
            'cvv'            => '200',
            'options'        => array('verifyCard' => true)
        ));

        Configuration::merchantId($old_merchant_id);
        Configuration::publicKey($old_public_key);
        Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $this->assertEquals(Transaction::CVV, $result->creditCardVerification->gatewayRejectionReason);
    }

    function testGatewayRejectionIsNullOnProcessorDecline()
    {
        $old_merchant_id = Configuration::merchantId();
        $old_public_key = Configuration::publicKey();
        $old_private_key = Configuration::privateKey();

        Configuration::merchantId('processing_rules_merchant_id');
        Configuration::publicKey('processing_rules_public_key');
        Configuration::privateKey('processing_rules_private_key');

        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'number'         => '5105105105105100',
            'expirationDate' => '05/2011',
            'cvv'            => '200',
            'options'        => array('verifyCard' => true)
        ));

        Configuration::merchantId($old_merchant_id);
        Configuration::publicKey($old_public_key);
        Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $this->assertNull($result->creditCardVerification->gatewayRejectionReason);
    }

    function createCreditCardViaTr($regularParams, $trParams)
    {
        $trData = TransparentRedirect::createCreditCardData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return TestHelper::submitTrRequest(
            CreditCard::createCreditCardUrl(),
            $regularParams,
            $trData
        );
    }

    function updateCreditCardViaTr($regularParams, $trParams)
    {
        $trData = TransparentRedirect::updateCreditCardData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return TestHelper::submitTrRequest(
            CreditCard::updateCreditCardUrl(),
            $regularParams,
            $trData
        );
    }

    function testPrepaidCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::PREPAID,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::PREPAID_YES, $result->creditCard->prepaid);
    }

    function testCommercialCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::COMMERCIAL,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::COMMERCIAL_YES, $result->creditCard->commercial);
    }

    function testDebitCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::DEBIT,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::DEBIT_YES, $result->creditCard->debit);
    }

    function testPayrollCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::PAYROLL,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::PAYROLL_YES, $result->creditCard->payroll);
    }

    function testHealthCareCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::HEALTHCARE,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::HEALTHCARE_YES, $result->creditCard->healthcare);
    }

    function testDurbinRegulatedCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::DURBIN_REGULATED,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::DURBIN_REGULATED_YES, $result->creditCard->durbinRegulated);
    }

    function testCountryOfIssuanceCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::COUNTRY_OF_ISSUANCE,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals("USA", $result->creditCard->countryOfIssuance);
    }

    function testIssuingBankCard()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::ISSUING_BANK,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals("NETWORK ONLY", $result->creditCard->issuingBank);
    }

    function testNegativeCardTypeIndicators()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::NO,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::PREPAID_NO, $result->creditCard->prepaid);
        $this->assertEquals(CreditCard::DURBIN_REGULATED_NO, $result->creditCard->durbinRegulated);
        $this->assertEquals(CreditCard::PAYROLL_NO, $result->creditCard->payroll);
        $this->assertEquals(CreditCard::DEBIT_NO, $result->creditCard->debit);
        $this->assertEquals(CreditCard::HEALTHCARE_NO, $result->creditCard->healthcare);
        $this->assertEquals(CreditCard::COMMERCIAL_NO, $result->creditCard->commercial);
    }

    function testUnknownCardTypeIndicators()
    {
        $customer = Customer::createNoValidate();
        $result = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Cardholder',
            'number'         => CardTypeIndicators::UNKNOWN,
            'expirationDate' => '05/12',
            'options'        => array('verifyCard' => true)
        ));
        $this->assertEquals(CreditCard::PREPAID_UNKNOWN, $result->creditCard->prepaid);
        $this->assertEquals(CreditCard::DURBIN_REGULATED_UNKNOWN, $result->creditCard->durbinRegulated);
        $this->assertEquals(CreditCard::PAYROLL_UNKNOWN, $result->creditCard->payroll);
        $this->assertEquals(CreditCard::DEBIT_UNKNOWN, $result->creditCard->debit);
        $this->assertEquals(CreditCard::HEALTHCARE_UNKNOWN, $result->creditCard->healthcare);
        $this->assertEquals(CreditCard::COMMERCIAL_UNKNOWN, $result->creditCard->commercial);
        $this->assertEquals(CreditCard::COUNTRY_OF_ISSUANCE_UNKNOWN, $result->creditCard->countryOfIssuance);
        $this->assertEquals(CreditCard::ISSUING_BANK_UNKNOWN, $result->creditCard->issuingBank);
    }
}
