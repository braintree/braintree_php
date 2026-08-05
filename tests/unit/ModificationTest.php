<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ModificationTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $modification = Braintree\Modification::factory([]);
        $this->assertInstanceOf('Braintree\Modification', $modification);
    }

    public function testFactory_setsAttributes()
    {
        $modification = Braintree\Modification::factory([
            'id' => 'mod_123',
            'amount' => '5.00',
            'numberOfBillingCycles' => 3,
            'neverExpires' => false,
            'quantity' => 1,
        ]);

        $this->assertEquals('mod_123', $modification->id);
        $this->assertEquals('5.00', $modification->amount);
        $this->assertEquals(3, $modification->numberOfBillingCycles);
        $this->assertFalse($modification->neverExpires);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $modification = Braintree\Modification::factory(['id' => 'mod_123', 'amount' => '5.00']);
        $this->assertStringContainsString('Modification', (string) $modification);
    }
}
