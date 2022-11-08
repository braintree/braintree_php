<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class SepaDirectDebitAccountTest extends Setup
{
    public function testReturnSepaDirectDebitAccount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$sepaDirectDebit;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $foundSepaDirectDebitAccount = $result->paymentMethod;

        $this->assertInstanceOf('Braintree\SepaDirectDebitAccount', $foundSepaDirectDebitAccount);
        $this->assertEquals($customer->id, $foundSepaDirectDebitAccount->customerId);
        $this->assertNotNull($foundSepaDirectDebitAccount->customerGlobalId);
        $this->assertNotNull($foundSepaDirectDebitAccount->globalId);
        $this->assertNotNull($foundSepaDirectDebitAccount->imageUrl);
        $this->assertNotNull($foundSepaDirectDebitAccount->token);
        $this->assertEquals('a-fake-mp-customer-id', $foundSepaDirectDebitAccount->merchantOrPartnerCustomerId);
        $this->assertEquals(true, $foundSepaDirectDebitAccount->default);
        $this->assertEquals('1234', $foundSepaDirectDebitAccount->last4);
        $this->assertEquals('a-fake-bank-reference-token', $foundSepaDirectDebitAccount->bankReferenceToken);
        $this->assertEquals('RECURRENT', $foundSepaDirectDebitAccount->mandateType);
        $this->assertEquals('DateTime', get_class($foundSepaDirectDebitAccount->createdAt));
        $this->assertEquals('DateTime', get_class($foundSepaDirectDebitAccount->updatedAt));
    }

    public function testFind()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$sepaDirectDebit;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $foundSepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::find($result->paymentMethod->token);

        $this->assertInstanceOf('Braintree\SepaDirectDebitAccount', $foundSepaDirectDebitAccount);
        $this->assertEquals($customer->id, $foundSepaDirectDebitAccount->customerId);
        $this->assertNotNull($foundSepaDirectDebitAccount->customerGlobalId);
        $this->assertNotNull($foundSepaDirectDebitAccount->globalId);
        $this->assertNotNull($foundSepaDirectDebitAccount->imageUrl);
        $this->assertNotNull($foundSepaDirectDebitAccount->token);
        $this->assertEquals('a-fake-mp-customer-id', $foundSepaDirectDebitAccount->merchantOrPartnerCustomerId);
        $this->assertEquals(true, $foundSepaDirectDebitAccount->default);
        $this->assertEquals('1234', $foundSepaDirectDebitAccount->last4);
        $this->assertEquals('a-fake-bank-reference-token', $foundSepaDirectDebitAccount->bankReferenceToken);
        $this->assertEquals('RECURRENT', $foundSepaDirectDebitAccount->mandateType);
        $this->assertEquals('DateTime', get_class($foundSepaDirectDebitAccount->createdAt));
        $this->assertEquals('DateTime', get_class($foundSepaDirectDebitAccount->updatedAt));
    }

    public function testSale_createsASaleUsingGivenToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$sepaDirectDebit;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
        ]);

        $pmtToken = $result->paymentMethod->token;
        $result = Braintree\SepaDirectDebitAccount::sale($pmtToken, [
            'amount' => '100.00',
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $details = $transaction->sepaDirectDebitAccountDetails;

        $this->assertEquals(Braintree\Transaction::SETTLING, $transaction->status);
        $this->assertEquals(Braintree\Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('a-fake-mp-customer-id', $details->merchantOrPartnerCustomerId);
        $this->assertEquals('1234', $details->last4);
        $this->assertEquals('a-fake-bank-reference-token', $details->bankReferenceToken);
        $this->assertEquals('RECURRENT', $details->mandateType);
        $this->assertEquals('USD', $details->transactionFeeCurrencyIsoCode);
        $this->assertEquals('0.01', $details->transactionFeeAmount);
        $this->assertEquals($pmtToken, $details->token);

        $this->assertNull($details->debugId);
        $this->assertNull($details->refundId);
        $this->assertNull($details->correlationId);
        $this->assertNull($details->settlementType);
        $this->assertNull($details->paypalV2OrderId);
        $this->assertNull($details->refundFromTransactionFeeAmount);
        $this->assertNull($details->refundFromTransactionFeeCurrencyIsoCode);

        $this->assertNotNull($details->captureId);
        $this->assertNotNull($details->globalId);
    }
}
