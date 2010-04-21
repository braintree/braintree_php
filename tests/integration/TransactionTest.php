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
        $this->assertEquals('sale', $transaction->type);
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
        $this->assertEquals('91526', $errors[0]->code);
        $this->assertEquals('Custom field is invalid: invalidKey.', $errors[0]->message);
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
        $this->assertEquals('sale', $transaction->type);
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
        $this->assertEquals('credit', $transaction->type);
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
        $this->assertEquals('credit', $transaction->type);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
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
        $this->assertEquals('91504', $errors[0]->code);
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
        $this->assertEquals('sale', $transaction->type);
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
                    'type' => 'sale',
                    'amount' => '100.00'
                )
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals('sale', $result->transaction->type);
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
                    'type' => 'sale',
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
        $this->assertEquals('sale', $result->transaction->type);
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
                'transaction' => array('type' => 'sale')
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->forKey('customer')->onAttribute('firstName');
        $this->assertEquals('81608', $errors[0]->code);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('number');
        $this->assertEquals('81716', $errors[0]->code);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals('81709', $errors[0]->code);
    }

    function testRefund()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals('credit', $result->transaction->type);
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

    function testBasicSearch()
    {
        $collection = Braintree_Transaction::search("411111");
        $this->assertTrue($collection->totalItems() > 1);
    }
}
?>

