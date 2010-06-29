<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CreditCardTest extends PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
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
        $customer = Braintree_Customer::createNoValidate();
        $card1 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;
        $this->assertTrue($card1->isDefault());

        $card2 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'options' => array(
                'makeDefault' => true
            )
        ))->creditCard;

        $card1 = Braintree_CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    function testCreate_withExpirationMonthAndYear()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationMonth' => '05',
            'expirationYear' => '2011'
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
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $token
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($token, $result->creditCard->token);
        $this->assertEquals($token, Braintree_CreditCard::find($token)->token);
    }

    function testCreate_withCardVerification()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => array('verifyCard' => true)
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
    }

    function testCreate_withCardVerificationAndSpecificMerchantAccount()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'options' => array('verificationMerchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(), 'verifyCard' => true)
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
    }

    function testCreate_withBillingAddress()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Peter Tomlin',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
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

    function testCreate_withValidationErrors()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'expirationDate' => 'invalid',
            'billingAddress' => array(
                'countryName' => 'Tuvalu',
                'countryCodeAlpha2' => 'US'
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_EXPIRATION_DATE_IS_INVALID, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Credit card number is required\./', $result->message));
        $this->assertEquals(1, preg_match('/Customer ID is required\./', $result->message));
        $this->assertEquals(1, preg_match('/Expiration date is invalid\./', $result->message));

        $errors = $result->errors->forKey('creditCard')->forKey('billingAddress')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    function testCreateNoValidate_throwsIfValidationsFail()
    {

        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        $customer = Braintree_Customer::createNoValidate();
        Braintree_CreditCard::createNoValidate(array(
            'expirationDate' => 'invalid',
        ));
    }

    function testCreateNoValidate_returnsCreditCardIfValid()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
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
        Braintree_TestHelper::suppressDeprecationWarnings();
        $customer = Braintree_Customer::createNoValidate();
        $queryString = $this->createCreditCardViaTr(
            array(
                'credit_card' => array(
                    'number' => '5105105105105100',
                    'expiration_date' => '05/12'
                )
            ),
            array(
                'creditCard' => array(
                    'customerId' => $customer->id
                )
            )
        );
        $result = Braintree_CreditCard::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('510510', $result->creditCard->bin);
        $this->assertEquals('5100', $result->creditCard->last4);
        $this->assertEquals('05/2012', $result->creditCard->expirationDate);
    }

    function testCreateFromTransparentRedirect_withDefault()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $customer = Braintree_Customer::createNoValidate();
        $queryString = $this->createCreditCardViaTr(
            array(
                'credit_card' => array(
                    'number' => '5105105105105100',
                    'expiration_date' => '05/12',
                    'options' => array('make_default' => true)
                )
            ),
            array(
                'creditCard' => array(
                    'customerId' => $customer->id
                )
            )
        );
        $result = Braintree_CreditCard::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->creditCard->isDefault());
    }

    function testUpdateFromTransparentRedirect()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $queryString = $this->updateCreditCardViaTr(
            array(
                'credit_card' => array(
                    'number' => '4111111111111111',
                    'expiration_date' => '01/11'
                )
            ),
            array('paymentMethodToken' => $creditCard->token)
        );
        $result = Braintree_CreditCard::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('411111', $result->creditCard->bin);
        $this->assertEquals('1111', $result->creditCard->last4);
        $this->assertEquals('01/2011', $result->creditCard->expirationDate);
    }

    function testUpdateFromTransparentRedirect_withDefault()
    {
        $customer = Braintree_Customer::createNoValidate();
        $card1 = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $card2 = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
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
        $result = Braintree_CreditCard::updateFromTransparentRedirect($queryString);
        $this->assertFalse(Braintree_CreditCard::find($card1->token)->isDefault());
        $this->assertTrue(Braintree_CreditCard::find($card2->token)->isDefault());
    }

    function testUpdateFromTransparentRedirect_andUpdateExistingBillingAddress()
    {
        $customer = Braintree_Customer::createNoValidate();
        $card = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'firstName' => 'Drew',
                'lastName' => 'Smith',
                'company' => 'Smith Co.',
                'streetAddress' => '123 Old St',
                'extendedAddress' => 'Suite 101',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60622',
                'countryName' => 'United States of America'
            )
        ));

        $queryString = $this->updateCreditCardViaTr(
            array(),
            array(
                'paymentMethodToken' => $card->token,
                'creditCard' => array(
                    'billingAddress' => array(
                        'streetAddress' => '123 New St',
                        'locality' => 'St. Louis',
                        'region' => 'MO',
                        'postalCode' => '63119',
                        'options' => array(
                            'updateExisting' => True
                        )
                    )
                )
            )
        );
        $result = Braintree_CreditCard::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $card = $result->creditCard;
        $this->assertEquals(1, sizeof(Braintree_Customer::find($customer->id)->addresses));
        $this->assertEquals('123 New St', $card->billingAddress->streetAddress);
        $this->assertEquals('St. Louis', $card->billingAddress->locality);
        $this->assertEquals('MO', $card->billingAddress->region);
        $this->assertEquals('63119', $card->billingAddress->postalCode);
    }

    function testSale_createsASaleUsingGivenToken()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $result = Braintree_CreditCard::sale($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    function testSaleNoValidate_createsASaleUsingGivenToken()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree_CreditCard::saleNoValidate($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    function testSaleNoValidate_throwsIfInvalid()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        Braintree_CreditCard::saleNoValidate($creditCard->token, array(
            'amount' => 'invalid'
        ));
    }

    function testCredit_createsACreditUsingGivenToken()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $result = Braintree_CreditCard::credit($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    function testCreditNoValidate_createsACreditUsingGivenToken()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree_CreditCard::creditNoValidate($creditCard->token, array(
            'amount' => '100.00'
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction->type);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    function testCreditNoValidate_throwsIfInvalid()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        Braintree_CreditCard::creditNoValidate($creditCard->token, array(
            'amount' => 'invalid'
        ));
    }

    function testExpired()
    {
        $collection = Braintree_CreditCard::expired();
        $this->assertTrue($collection->maximumCount() > 1);

        $arr = array();
        foreach($collection as $creditCard) {
            $this->assertTrue($creditCard->isExpired());
            array_push($arr, $creditCard->token);
        }
        $uniqueCreditCardTokens = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($uniqueCreditCardTokens));
    }


    function testExpiringBetween()
    {
        $collection = Braintree_CreditCard::expiringBetween(
            mktime(0, 0, 0, 1, 1, 2009),
            mktime(23, 59, 59, 12, 31, 2009)
        );
        $this->assertTrue($collection->maximumCount() > 1);

        $arr = array();
        foreach($collection as $creditCard) {
            $this->assertEquals('2009', $creditCard->expirationYear);
            array_push($arr, $creditCard->token);
        }
        $uniqueCreditCardTokens = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($uniqueCreditCardTokens));
    }

    function testFind()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($result->success);
        $creditCard = Braintree_CreditCard::find($result->creditCard->token);
        $this->assertEquals($customer->id, $creditCard->customerId);
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Cardholder', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals(array(), $creditCard->subscriptions);
    }

    function testFindReturnsAssociatedSubscriptions()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'firstName' => 'Drew',
                'lastName' => 'Smith',
                'company' => 'Smith Co.',
                'streetAddress' => '1 E Main St',
                'extendedAddress' => 'Suite 101',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60622',
                'countryName' => 'United States of America'
            )
        ));
        $id = strval(rand());
        Braintree_Subscription::create(array(
            'id' => $id,
            'paymentMethodToken' => $result->creditCard->token,
            'planId' => 'integration_trialless_plan',
            'price' => '1.00'
        ));
        $creditCard = Braintree_CreditCard::find($result->creditCard->token);
        $this->assertEquals($id, $creditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $creditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $creditCard->subscriptions[0]->price);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_CreditCard::find('invalid-token');
    }

    function testUpdate()
    {
        $customer = Braintree_Customer::createNoValidate();
        $createResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($createResult->success);
        $updateResult = Braintree_CreditCard::update($createResult->creditCard->token, array(
            'cardholderName' => 'New Cardholder',
            'number' => '4111111111111111',
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
        $customer = Braintree_Customer::createNoValidate();
        $initialCreditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $result = Braintree_CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            ),
            'options' => array('verifyCard' => true)
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('I', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
        $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    function testUpdate_withCardVerificationAndSpecificMerchantAccount()
    {
        $customer = Braintree_Customer::createNoValidate();
        $initialCreditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $result = Braintree_CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            ),
            'options' => array(
                'verificationMerchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
                'verifyCard' => true
            )
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    function testUpdate_createsNewBillingAddressByDefault()
    {
        $customer = Braintree_Customer::createNoValidate();
        $initialCreditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ))->creditCard;

        $updatedCreditCard = Braintree_CreditCard::update($initialCreditCard->token, array(
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
        $customer = Braintree_Customer::createNoValidate();
        $initialCreditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'billingAddress' => array(
                'countryName' => 'Turkey',
                'countryCodeAlpha2' => 'TR',
                'countryCodeAlpha3' => 'TUR',
                'countryCodeNumeric' => '792',
            )
        ))->creditCard;

        $updatedCreditCard = Braintree_CreditCard::update($initialCreditCard->token, array(
            'billingAddress' => array(
                'countryName' => 'Thailand',
                'countryCodeAlpha2' => 'TH',
                'countryCodeAlpha3' => 'THA',
                'countryCodeNumeric' => '764',
                'options' => array(
                    'updateExisting' => True
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

        $customer = Braintree_Customer::createNoValidate();
        $createResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'token' => $oldToken,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($createResult->success);
        $updateResult = Braintree_CreditCard::update($oldToken, array(
            'token' => $newToken
        ));
        $this->assertEquals($customer->id, $updateResult->creditCard->customerId);
        $this->assertEquals($newToken, $updateResult->creditCard->token);
        $this->assertEquals($newToken, Braintree_CreditCard::find($newToken)->token);
    }

    function testUpdateNoValidate()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $updatedCard = Braintree_CreditCard::updateNoValidate($creditCard->token, array(
            'cardholderName' => 'New Cardholder',
            'number' => '4111111111111111',
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
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Old Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        Braintree_CreditCard::updateNoValidate($creditCard->token, array(
            'number' => 'invalid',
        ));
    }

    function testUpdate_withDefault()
    {
        $customer = Braintree_Customer::createNoValidate();
        $card1 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;
        $card2 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $this->assertTrue($card1->isDefault());
        $this->assertFalse($card2->isDefault());

        Braintree_CreditCard::update($card2->token, array(
            'options' => array('makeDefault' => true)
        ))->creditCard;

        $this->assertFalse(Braintree_CreditCard::find($card1->token)->isDefault());
        $this->assertTrue(Braintree_CreditCard::find($card2->token)->isDefault());
    }

    function testDelete_deletesThePaymentMethod()
    {
        $customer = Braintree_Customer::createNoValidate(array());
        $creditCard = Braintree_CreditCard::createNoValidate(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        Braintree_CreditCard::find($creditCard->token);
        Braintree_CreditCard::delete($creditCard->token);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_CreditCard::find($creditCard->token);
    }

    function testGatewayRejectionOnCVV()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '4111111111111111',
            'expirationDate' => '05/2011',
            'cvv' => '200',
            'options' => array('verifyCard' => true)
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::CVV, $result->creditCardVerification->gatewayRejectionReason);
    }

    function testGatewayRejectionIsNullOnProcessorDecline()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'cvv' => '200',
            'options' => array('verifyCard' => true)
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $this->assertNull($result->creditCardVerification->gatewayRejectionReason);
    }

    function createCreditCardViaTr($regularParams, $trParams)
    {
        $trData = Braintree_TransparentRedirect::createCreditCardData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            Braintree_CreditCard::createCreditCardUrl(),
            $regularParams,
            $trData
        );
    }

    function updateCreditCardViaTr($regularParams, $trParams)
    {
        $trData = Braintree_TransparentRedirect::updateCreditCardData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            Braintree_CreditCard::updateCreditCardUrl(),
            $regularParams,
            $trData
        );
    }
}
?>

