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

    function testSale_withCustomFields()
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
        $this->assertEquals('custom value', $customFields['storeMe']);
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
            'amount' => '2000.00',
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

    function testCreateFromTransparentRedirect_withParamsInTrData()
    {
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
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_NUMBER_INVALID_LENGTH, $errors[0]->code);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED, $errors[0]->code);
    }

    function testRefund()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(Braintree_Transaction::CREDIT, $result->transaction->type);
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

    function createTransactionViaTr($regularParams, $trParams)
    {
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

    function testBasicSearchWithNoResults()
    {
        $collection = Braintree_Transaction::search("badsearch");
        $this->assertEquals(0, $collection->_approximateCount());

        $arr = array();
        foreach($collection as $key => $transaction) {
            array_push($arr, $transaction->id);
        }
        $this->assertEquals(0, count($arr));
    }

    function testBasicSearchWithManyResults()
    {
        $collection = Braintree_Transaction::search("411111");
        $this->assertTrue($collection->_approximateCount() > 100);

        $arr = array();
        foreach($collection as $transaction) {
            array_push($arr, $transaction->id);
        }
        $unique_transaction_ids = array_unique(array_values($arr));
        $this->assertEquals($collection->_approximateCount(), count($unique_transaction_ids));
    }

    function testBasicSearchWithMultipleIterations()
    {
        $collection = Braintree_Transaction::search("411111");
        $this->assertTrue($collection->_approximateCount() > 100);

        $arr_1 = array();
        foreach($collection as $transaction) {
            array_push($arr_1, $transaction->id);
        }
        $unique_transaction_ids_1 = array_unique(array_values($arr_1));

        $arr_2 = array();
        foreach($collection as $transaction) {
            array_push($arr_2, $transaction->id);
        }
        $unique_transaction_ids_2 = array_unique(array_values($arr_2));

        $this->assertEquals($unique_transaction_ids_1, $unique_transaction_ids_2);
    }
}
?>

