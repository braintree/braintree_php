<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransactionTest extends PHPUnit_Framework_TestCase
{
    function testSale()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
        $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
    }

    function testSale_withAllAttributes()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'orderId' => '123',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/2011',
                'cvv' => '123'
            ),
            'customer' => array(
                'firstName' => 'Dan',
                'lastName' => 'Smith',
                'company' => 'Braintree Payment Solutions',
                'email' => 'dan@example.com',
                'phone' => '419-555-1234',
                'fax' => '419-555-1235',
                'website' => 'http://braintreepaymentsolutions.com'
            ),
            'billing' => array(
                'firstName' => 'Carl',
                'lastName' => 'Jones',
                'company' => 'Braintree',
                'streetAddress' => '123 E Main St',
                'extendedAddress' => 'Suite 403',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60622',
                'countryName' => 'United States of America',
                'countryCodeAlpha2' => 'US',
                'countryCodeAlpha3' => 'USA',
                'countryCodeNumeric' => '840'
            ),
            'shipping' => array(
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
            )
      ));
      $this->assertTrue($result->success);
      $transaction = $result->transaction;

      $this->assertNotNull($transaction->id);
      $this->assertNotNull($transaction->createdAt);
      $this->assertNotNull($transaction->updatedAt);
      $this->assertNull($transaction->refundId);

      $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
      $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
      $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
      $this->assertEquals('100.00', $transaction->amount);
      $this->assertEquals('USD', $transaction->currencyIsoCode);
      $this->assertEquals('123', $transaction->orderId);
      $this->assertEquals('MasterCard', $transaction->creditCardDetails->cardType);
      $this->assertEquals('1000', $transaction->processorResponseCode);
      $this->assertEquals('Approved', $transaction->processorResponseText);

      $this->assertEquals('M', $transaction->avsPostalCodeResponseCode);
      $this->assertEquals('M', $transaction->avsStreetAddressResponseCode);
      $this->assertEquals('M', $transaction->cvvResponseCode);

      $this->assertEquals('Dan', $transaction->customerDetails->firstName);
      $this->assertEquals('Smith', $transaction->customerDetails->lastName);
      $this->assertEquals('Braintree Payment Solutions', $transaction->customerDetails->company);
      $this->assertEquals('dan@example.com', $transaction->customerDetails->email);
      $this->assertEquals('419-555-1234', $transaction->customerDetails->phone);
      $this->assertEquals('419-555-1235', $transaction->customerDetails->fax);
      $this->assertEquals('http://braintreepaymentsolutions.com', $transaction->customerDetails->website);

      $this->assertEquals('Carl', $transaction->billingDetails->firstName);
      $this->assertEquals('Jones', $transaction->billingDetails->lastName);
      $this->assertEquals('Braintree', $transaction->billingDetails->company);
      $this->assertEquals('123 E Main St', $transaction->billingDetails->streetAddress);
      $this->assertEquals('Suite 403', $transaction->billingDetails->extendedAddress);
      $this->assertEquals('Chicago', $transaction->billingDetails->locality);
      $this->assertEquals('IL', $transaction->billingDetails->region);
      $this->assertEquals('60622', $transaction->billingDetails->postalCode);
      $this->assertEquals('United States of America', $transaction->billingDetails->countryName);
      $this->assertEquals('US', $transaction->billingDetails->countryCodeAlpha2);
      $this->assertEquals('USA', $transaction->billingDetails->countryCodeAlpha3);
      $this->assertEquals('840', $transaction->billingDetails->countryCodeNumeric);

      $this->assertEquals('Andrew', $transaction->shippingDetails->firstName);
      $this->assertEquals('Mason', $transaction->shippingDetails->lastName);
      $this->assertEquals('Braintree', $transaction->shippingDetails->company);
      $this->assertEquals('456 W Main St', $transaction->shippingDetails->streetAddress);
      $this->assertEquals('Apt 2F', $transaction->shippingDetails->extendedAddress);
      $this->assertEquals('Bartlett', $transaction->shippingDetails->locality);
      $this->assertEquals('IL', $transaction->shippingDetails->region);
      $this->assertEquals('60103', $transaction->shippingDetails->postalCode);
      $this->assertEquals('United States of America', $transaction->shippingDetails->countryName);
      $this->assertEquals('US', $transaction->shippingDetails->countryCodeAlpha2);
      $this->assertEquals('USA', $transaction->shippingDetails->countryCodeAlpha3);
      $this->assertEquals('840', $transaction->shippingDetails->countryCodeNumeric);

      $this->assertNotNull($transaction->processorAuthorizationCode);
      $this->assertEquals('510510', $transaction->creditCardDetails->bin);
      $this->assertEquals('5100', $transaction->creditCardDetails->last4);
      $this->assertEquals('510510******5100', $transaction->creditCardDetails->maskedNumber);
      $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
    }

    function testSale_withCustomFields()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'store_me' => 'custom value'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $customFields = $transaction->customFields;
        $this->assertEquals('custom value', $customFields['store_me']);
    }

    function testSale_underscoresAllCustomFields()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'storeMe' => 'custom value'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $customFields = $transaction->customFields;
        $this->assertEquals('custom value', $customFields['store_me']);
    }

    function testSale_withInvalidCustomField()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'invalidKey' => 'custom value'
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('customFields');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_CUSTOM_FIELD_IS_INVALID, $errors[0]->code);
        $this->assertEquals('Custom field is invalid: invalidKey.', $errors[0]->message);
    }

    function testSale_withMerchantAccountId()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testSale_withoutMerchantAccountIdFallsBackToDefault()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
    }


    function testSaleNoValidate()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testSale_withProcessorDecline()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$decline,
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::PROCESSOR_DECLINED, $result->transaction->status);
    }

    function testSale_withExistingCustomer()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ))->customer;

        $transaction = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->transaction;
        $this->assertEquals($transaction->creditCardDetails->maskedNumber, '401288******1881');
        $this->assertNull($transaction->vaultCreditCard());
    }

    function testSale_withExistingCustomer_storeInVault()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ))->customer;

        $transaction = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'storeInVault' => true
            )
        ))->transaction;
        $this->assertEquals($transaction->creditCardDetails->maskedNumber, '401288******1881');
        $this->assertEquals($transaction->vaultCreditCard()->maskedNumber, '401288******1881');
    }

    function testCredit()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testCreditNoValidate()
    {
        $transaction = Braintree_Transaction::creditNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction->type);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testCredit_withMerchantAccountId()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testCredit_withoutMerchantAccountIdFallsBackToDefault()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testSubmitForSettlement_nullAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submitResult = Braintree_Transaction::submitForSettlement($transaction->id);
        $this->assertEquals(true, $submitResult->success);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submitResult->transaction->status);
        $this->assertEquals('100.00', $submitResult->transaction->amount);
    }

    function testSubmitForSettlement_withAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submitResult = Braintree_Transaction::submitForSettlement($transaction->id, '50.00');
        $this->assertEquals(true, $submitResult->success);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submitResult->transaction->status);
        $this->assertEquals('50.00', $submitResult->transaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenValidWithoutAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submittedTransaction = Braintree_Transaction::submitForSettlementNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submittedTransaction->status);
        $this->assertEquals('100.00', $submittedTransaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenValidWithAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submittedTransaction = Braintree_Transaction::submitForSettlementNoValidate($transaction->id, '99.00');
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submittedTransaction->status);
        $this->assertEquals('99.00', $submittedTransaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenInvalid()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        $submittedTransaction = Braintree_Transaction::submitForSettlementNoValidate($transaction->id, '101.00');
    }

    function testVoid()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voidResult = Braintree_Transaction::void($transaction->id);
        $this->assertEquals(true, $voidResult->success);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voidResult->transaction->status);
    }

    function test_countryValidationError_inconsistency()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeAlpha2' => 'AS',
                'countryCodeAlpha3' => 'USA'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    function test_countryValidationError_incorrectAlpha2()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeAlpha2' => 'ZZ'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeAlpha2');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_CODE_ALPHA2_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function test_countryValidationError_incorrectAlpha3()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeAlpha3' => 'ZZZ'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeAlpha3');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_CODE_ALPHA3_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function test_countryValidationError_incorrectNumericCode()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeNumeric' => '000'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeNumeric');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_CODE_NUMERIC_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function testVoid_withValidationError()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided->status);
        $result = Braintree_Transaction::void($transaction->id);
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_CANNOT_BE_VOIDED, $errors[0]->code);
    }

    function testVoidNoValidate()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided->status);
    }

    function testVoidNoValidate_throwsIfNotInvalid()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided->status);
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
    }

    function testFind()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $transaction = Braintree_Transaction::find($result->transaction->id);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testSale_storeInVault()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer' => array(
                'firstName' => 'Dan',
                'lastName' => 'Smith',
                'company' => 'Braintree Payment Solutions',
                'email' => 'dan@example.com',
                'phone' => '419-555-1234',
                'fax' => '419-555-1235',
                'website' => 'http://getbraintree.com'
            ),
            'options' => array(
                'storeInVault' => true
            )
        ));
        $this->assertNotNull($transaction->creditCardDetails->token);
        $creditCard = $transaction->vaultCreditCard();
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('Card Holder', $creditCard->cardholderName);
        $customer = $transaction->vaultCustomer();
        $this->assertEquals('Dan', $customer->firstName);
        $this->assertEquals('Smith', $customer->lastName);
        $this->assertEquals('Braintree Payment Solutions', $customer->company);
        $this->assertEquals('dan@example.com', $customer->email);
        $this->assertEquals('419-555-1234', $customer->phone);
        $this->assertEquals('419-555-1235', $customer->fax);
        $this->assertEquals('http://getbraintree.com', $customer->website);
    }

    function testCreateFromTransparentRedirect()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'customer' => array(
                        'first_name' => 'First'
                    ),
                    'credit_card' => array(
                        'number' => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
                'transaction' => array(
                    'type' => Braintree_Transaction::SALE,
                    'amount' => '100.00'
                )
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateFromTransparentRedirectWithInvalidParams()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'bad_key' => 'bad_value',
                    'customer' => array(
                        'first_name' => 'First'
                    ),
                    'credit_card' => array(
                        'number' => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
                'transaction' => array(
                    'type' => Braintree_Transaction::SALE,
                    'amount' => '100.00'
                )
            )
        );
        try {
            $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
            $this->fail();
        } catch (Braintree_Exception_Authorization $e) {
            $this->assertEquals("Invalid params: transaction[bad_key]", $e->getMessage());
        }
    }

    function testCreateFromTransparentRedirect_withParamsInTrData()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
            ),
            array(
                'transaction' => array(
                    'type' => Braintree_Transaction::SALE,
                    'amount' => '100.00',
                    'customer' => array(
                        'firstName' => 'First'
                    ),
                    'creditCard' => array(
                        'number' => '5105105105105100',
                        'expirationDate' => '05/12'
                    )
                )
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateFromTransparentRedirect_withValidationErrors()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'customer' => array(
                        'first_name' => str_repeat('x', 256),
                    ),
                    'credit_card' => array(
                        'number' => 'invalid',
                        'expiration_date' => ''
                    )
                )
            ),
            array(
                'transaction' => array('type' => Braintree_Transaction::SALE)
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->forKey('customer')->onAttribute('firstName');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_FIRST_NAME_IS_TOO_LONG, $errors[0]->code);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('number');
        $this->assertTrue(count($errors) > 0);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED, $errors[0]->code);
    }

    function testRefund()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id);
        $this->assertTrue($result->success);
        $refund = $result->transaction;
        $this->assertEquals(Braintree_Transaction::CREDIT, $refund->type);
        $this->assertEquals($transaction->id, $refund->refundedTransactionId);
        $this->assertEquals($refund->id, Braintree_Transaction::find($transaction->id)->refundId);
    }

    function testRefundWithPartialAmount()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id, '50.00');
        $this->assertTrue($result->success);
        $this->assertEquals(Braintree_Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals("50.00", $result->transaction->amount);
    }

    function testRefundWithUnsuccessfulPartialAmount()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id, '150.00');
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('amount');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_REFUND_AMOUNT_IS_TOO_LARGE,
            $errors[0]->code
        );
    }

    function testGatewayRejectionOnAvs()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'billing' => array(
                'streetAddress' => '200 2nd Street'
            ),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Braintree_Transaction::AVS, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnAvsAndCvv()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'billing' => array(
                'postalCode' => '20000'
            ),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '200'
            )
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Braintree_Transaction::AVS_AND_CVV, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnCvv()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '200'
            )
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Braintree_Transaction::CVV, $transaction->gatewayRejectionReason);
    }


    function createTransactionViaTr($regularParams, $trParams)
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $trData = Braintree_TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            Braintree_Transaction::createTransactionUrl(),
            $regularParams,
            $trData
        );
    }

    function createTransactionToRefund()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array('submitForSettlement' => true)
        ));
        Braintree_Http::put('/transactions/' . $transaction->id . '/settle');
        return $transaction;
    }

}
?>

