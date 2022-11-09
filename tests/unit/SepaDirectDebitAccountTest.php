<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class SepaDirectDebitAccountTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->expectError();
        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory([]);
        $sepaDirectDebitAccount->foo;
    }

    public function testIsDefault()
    {
        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory(['default' => true]);
        $this->assertTrue($sepaDirectDebitAccount->isDefault());

        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory(['default' => false]);
        $this->assertFalse($sepaDirectDebitAccount->isDefault());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\SepaDirectDebitAccount::find('');
    }
}
