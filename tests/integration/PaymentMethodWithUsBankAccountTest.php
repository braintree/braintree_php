<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class PaymentMethodWithUsBankAccountTest extends Setup
{
    public function testCreate_fromUsBankAccountNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Test\Helper::generateValidUsBankAccountNonce(),
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::usBankMerchantAccount()
            ]
        ]);

        $usBankAccount = $result->paymentMethod;
        $this->assertEquals('021000021', $usBankAccount->routingNumber);
        $this->assertEquals('1234', $usBankAccount->last4);
        $this->assertEquals('checking', $usBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $usBankAccount->accountHolderName);
        $this->assertMatchesRegularExpression('/CHASE/', $usBankAccount->bankName);
        $this->assertEquals(true, $usBankAccount->verified);

        $this->assertEquals(1, count($usBankAccount->verifications));

        $verification = $usBankAccount->verifications[0];

        $this->assertEquals(Braintree\Result\UsBankAccountVerification::VERIFIED, $verification->status);
        $this->assertEquals(Braintree\Result\UsBankAccountVerification::INDEPENDENT_CHECK, $verification->verificationMethod);
    }

    public function testCreate_fromUsBankAccountNonceWithVerification()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Test\Helper::generateValidUsBankAccountNonce('1000000000'),
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::usBankMerchantAccount(),
                'usBankAccountVerificationMethod' => Braintree\Result\UsBankAccountVerification::NETWORK_CHECK,
            ]
        ]);

        $usBankAccount = $result->paymentMethod;
        $this->assertEquals('021000021', $usBankAccount->routingNumber);
        $this->assertEquals('0000', $usBankAccount->last4);
        $this->assertEquals('checking', $usBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $usBankAccount->accountHolderName);
        $this->assertMatchesRegularExpression('/CHASE/', $usBankAccount->bankName);
        $this->assertEquals(true, $usBankAccount->verified);

        $this->assertEquals(1, count($usBankAccount->verifications));

        $verification = $usBankAccount->verifications[0];

        $this->assertEquals(Braintree\Result\UsBankAccountVerification::VERIFIED, $verification->status);
        $this->assertEquals(Braintree\Result\UsBankAccountVerification::NETWORK_CHECK, $verification->verificationMethod);
    }

    public function testCreate_fromPlaidUsBankAccountNonce()
    {
        $this->markTestSkipped( 'Skipping until we have a more stable CI env' );
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Test\Helper::generatePlaidUsBankAccountNonce(),
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::usBankMerchantAccount()
            ]
        ]);

        $usBankAccount = $result->paymentMethod;
        $this->assertEquals('011000015', $usBankAccount->routingNumber);
        $this->assertEquals('0000', $usBankAccount->last4);
        $this->assertEquals('checking', $usBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $usBankAccount->accountHolderName);
        $this->assertMatchesRegularExpression('/FEDERAL/', $usBankAccount->bankName);
        $this->assertEquals(true, $usBankAccount->verified);

        $this->assertEquals(1, count($usBankAccount->verifications));

        $verification = $usBankAccount->verifications[0];

        $this->assertEquals(Braintree\Result\UsBankAccountVerification::VERIFIED, $verification->status);
        $this->assertEquals(Braintree\Result\UsBankAccountVerification::TOKENIZED_CHECK, $verification->verificationMethod);
    }

    public function testFind_returnsUsBankAccount()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Test\Helper::generateValidUsBankAccountNonce(),
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::usBankMerchantAccount()
            ]
        ]);

        $foundUsBankAccount = Braintree\PaymentMethod::find($result->paymentMethod->token);
        $this->assertInstanceOf('Braintree\UsBankAccount', $foundUsBankAccount);
        $this->assertEquals('021000021', $foundUsBankAccount->routingNumber);
        $this->assertEquals('1234', $foundUsBankAccount->last4);
        $this->assertEquals('checking', $foundUsBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $foundUsBankAccount->accountHolderName);
        $this->assertMatchesRegularExpression('/CHASE/', $foundUsBankAccount->bankName);
    }

    public function testCompliantCreate_fromUsBankAccountNonce()
    {
        Test\Helper::integration2MerchantConfig();

        $customer = Braintree\Customer::create([
            'firstName' => 'Joe',
            'lastName' => 'Brown'
        ])->customer;

        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Test\Helper::generateValidUsBankAccountNonce(),
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::anotherUsBankMerchantAccount()
            ]
        ]);

        $usBankAccount = $result->paymentMethod;
        $this->assertEquals('021000021', $usBankAccount->routingNumber);
        $this->assertEquals('1234', $usBankAccount->last4);
        $this->assertEquals('checking', $usBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $usBankAccount->accountHolderName);
        $this->assertMatchesRegularExpression('/CHASE/', $usBankAccount->bankName);
        $this->assertEquals(false, $usBankAccount->verified);

        $this->assertEquals(0, count($usBankAccount->verifications));
        self::integrationMerchantConfig();
    }

}
