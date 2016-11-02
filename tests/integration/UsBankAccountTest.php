<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class UsBankAccountAccountTest extends Setup
{


    public function testReturnUsBankAccount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Test\Helper::generateValidUsBankAccountNonce();

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $foundUsBankAccount = $result->paymentMethod;
        $this->assertInstanceOf('Braintree\UsBankAccount', $foundUsBankAccount);
        $this->assertEquals('123456789', $foundUsBankAccount->routingNumber);
        $this->assertEquals('1234', $foundUsBankAccount->last4);
        $this->assertEquals('checking', $foundUsBankAccount->accountType);
        $this->assertEquals('PayPal Checking - 1234', $foundUsBankAccount->accountDescription);
        $this->assertEquals('Dan Schulman', $foundUsBankAccount->accountHolderName);
        $this->assertEquals('UNKNOWN', $foundUsBankAccount->bankName);
    }

    public function testFind()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Test\Helper::generateValidUsBankAccountNonce();

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $foundUsBankAccount= Braintree\UsBankAccount::find($result->paymentMethod->token);
        $this->assertInstanceOf('Braintree\UsBankAccount', $foundUsBankAccount);
        $this->assertEquals('123456789', $foundUsBankAccount->routingNumber);
        $this->assertEquals('1234', $foundUsBankAccount->last4);
        $this->assertEquals('checking', $foundUsBankAccount->accountType);
        $this->assertEquals('PayPal Checking - 1234', $foundUsBankAccount->accountDescription);
        $this->assertEquals('Dan Schulman', $foundUsBankAccount->accountHolderName);
        $this->assertEquals('UNKNOWN', $foundUsBankAccount->bankName);
    }

    public function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        Braintree\UsBankAccount::find(Test\Helper::generateInvalidUsBankAccountNonce());
    }

    public function testSale_createsASaleUsingGivenToken()
    {
        $customer = Braintree\Customer::createNoValidate();
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Test\Helper::generateValidUsBankAccountNonce();

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ]);

        $result = Braintree\UsBankAccount::sale($result->paymentMethod->token, [
            'merchantAccountId' => 'us_bank_merchant_account',
            'amount' => '100.00'
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree\Transaction::SETTLEMENT_PENDING, $transaction->status);
        $this->assertEquals(Braintree\Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('123456789', $transaction->usBankAccount->routingNumber);
        $this->assertEquals('1234', $transaction->usBankAccount->last4);
        $this->assertEquals('checking', $transaction->usBankAccount->accountType);
        $this->assertEquals('PayPal Checking - 1234', $transaction->usBankAccount->accountDescription);
        $this->assertEquals('Dan Schulman', $transaction->usBankAccount->accountHolderName);
        $this->assertEquals('UNKNOWN', $transaction->usBankAccount->bankName);
    }
}
