<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class LocalPaymentCompletedTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $completed = Braintree\LocalPaymentCompleted::factory([]);
        $this->assertInstanceOf('Braintree\LocalPaymentCompleted', $completed);
    }

    public function testFactory_setsScalarAttributes()
    {
        $completed = Braintree\LocalPaymentCompleted::factory([
            'paymentId' => 'pay_123',
            'paymentContextId' => 'ctx_456',
            'payerId' => 'payer_789',
        ]);

        $this->assertEquals('pay_123', $completed->paymentId);
        $this->assertEquals('ctx_456', $completed->paymentContextId);
        $this->assertEquals('payer_789', $completed->payerId);
    }

    public function testFactory_blikAliasesDefaultToEmptyArray()
    {
        $completed = Braintree\LocalPaymentCompleted::factory([]);
        $this->assertEquals([], $completed->blikAliases);
    }

    public function testFactory_buildsBlikAliasObjects()
    {
        $completed = Braintree\LocalPaymentCompleted::factory([
            'blikAliases' => [
                ['key' => 'alias_1', 'label' => 'My Bank'],
                ['key' => 'alias_2', 'label' => 'Other Bank'],
            ],
        ]);

        $this->assertCount(2, $completed->blikAliases);
        $this->assertInstanceOf('Braintree\BlikAlias', $completed->blikAliases[0]);
        $this->assertEquals('alias_1', $completed->blikAliases[0]->key);
        $this->assertEquals('alias_2', $completed->blikAliases[1]->key);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $completed = Braintree\LocalPaymentCompleted::factory(['paymentId' => 'pay_123']);
        $this->assertStringContainsString('LocalPaymentCompleted', (string) $completed);
    }
}
