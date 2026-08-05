<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class MerchantTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $merchant = Braintree\Merchant::factory([]);
        $this->assertInstanceOf('Braintree\Merchant', $merchant);
    }

    public function testFactory_merchantAccountsDefaultToEmptyArray()
    {
        $merchant = Braintree\Merchant::factory([]);
        $this->assertEquals([], $merchant->merchantAccounts);
    }

    public function testFactory_buildsMerchantAccountObjects()
    {
        $merchant = Braintree\Merchant::factory([
            'merchantAccounts' => [
                ['id' => 'account_1', 'status' => 'active'],
                ['id' => 'account_2', 'status' => 'active'],
            ],
        ]);

        $this->assertCount(2, $merchant->merchantAccounts);
        $this->assertInstanceOf('Braintree\MerchantAccount', $merchant->merchantAccounts[0]);
        $this->assertEquals('account_1', $merchant->merchantAccounts[0]->id);
        $this->assertEquals('account_2', $merchant->merchantAccounts[1]->id);
    }

    public function testFactory_setsScalarAttributes()
    {
        $merchant = Braintree\Merchant::factory([
            'id' => 'merchant_1',
            'email' => 'merchant@example.com',
        ]);

        $this->assertEquals('merchant_1', $merchant->id);
        $this->assertEquals('merchant@example.com', $merchant->email);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $merchant = Braintree\Merchant::factory(['id' => 'merchant_1']);
        $this->assertStringContainsString('Merchant', (string) $merchant);
    }
}
