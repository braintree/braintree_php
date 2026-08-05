<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class RevokedPaymentMethodMetadataTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $metadata = Braintree\RevokedPaymentMethodMetadata::factory([
            'creditCard' => ['token' => 'tok_123', 'customerId' => 'cust_123', 'bin' => '411111', 'last4' => '1111'],
        ]);
        $this->assertInstanceOf('Braintree\RevokedPaymentMethodMetadata', $metadata);
    }

    public function testFactory_setsRevokedPaymentMethod()
    {
        $metadata = Braintree\RevokedPaymentMethodMetadata::factory([
            'creditCard' => ['token' => 'tok_123', 'customerId' => 'cust_123', 'bin' => '411111', 'last4' => '1111'],
        ]);
        $this->assertInstanceOf('Braintree\CreditCard', $metadata->revokedPaymentMethod);
    }

    public function testFactory_setsTokenFromPaymentMethod()
    {
        $metadata = Braintree\RevokedPaymentMethodMetadata::factory([
            'creditCard' => ['token' => 'tok_123', 'customerId' => 'cust_123', 'bin' => '411111', 'last4' => '1111'],
        ]);
        $this->assertEquals('tok_123', $metadata->token);
    }

    public function testFactory_setsCustomerIdFromPaymentMethod()
    {
        $metadata = Braintree\RevokedPaymentMethodMetadata::factory([
            'creditCard' => ['token' => 'tok_123', 'customerId' => 'cust_123', 'bin' => '411111', 'last4' => '1111'],
        ]);
        $this->assertEquals('cust_123', $metadata->customerId);
    }
}
