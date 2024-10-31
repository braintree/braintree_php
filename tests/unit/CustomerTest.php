<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class CustomerTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->expectError();
        $c = Braintree\Customer::factory([]);
        $c->foo;
    }

    # NEXT_MAJOR_VERSION Remove venmoSdkSession
    # The old venmo SDK class has been deprecated
    public function testUpdateSignature_doesNotAlterOptionsInCreditCardUpdateSignature()
    {
        Braintree\CustomerGateway::updateSignature();
        foreach (Braintree\CreditCardGateway::updateSignature() as $key => $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('options', $value)) {
                $this->assertEquals([
                    'failOnDuplicatePaymentMethod',
                    'failOnDuplicatePaymentMethodForCustomer',
                    'makeDefault',
                    'skipAdvancedFraudChecking',
                    'venmoSdkSession',  // Deprecated
                    'verificationAccountType',
                    'verificationAmount',
                    'verificationMerchantAccountId',
                    'verifyCard'
                ], $value['options']);
            }
        }
    }

    public function testCreateSignature_doesNotIncludeCustomerIdOnCreditCard()
    {
        $signature = Braintree\CustomerGateway::createSignature();
        $creditCardSignatures = array_filter($signature, 'Test\Unit\CustomerTest::findCreditCardArray');
        $creditCardSignature = array_shift($creditCardSignatures)['creditCard'];

        $this->assertNotContains('customerId', $creditCardSignature);
    }

    public function findCreditCardArray($el)
    {
        // phpcs:ignore
        return is_array($el) && array_key_exists('creditCard', $el);
    }

    public function testFindErrorsOnBlankId()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Customer::find('');
    }

    public function testFindErrorsOnWhitespaceId()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Customer::find('\t');
    }

    public function testCustomerWithSepaDebitAccount()
    {
        $sepaDebitAccount = Braintree\SepaDirectDebitAccount::factory([
            'last4' => '1234',
        ]);
        $customer = Braintree\Customer::factory([
            'sepaDebitAccounts' => $sepaDebitAccount,
        ]);
        $sepaDirectDebitAccount = $customer -> sepaDirectDebitAccounts[0];
        $this->assertEquals("1234", $sepaDirectDebitAccount -> toArray()['last4']);
    }
}
