<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class UsBankAccountTest extends Setup
{

    public function testIsDefault()
    {
        $usBankAccount = Braintree\UsBankAccount::factory(['default' => true]);
        $this->assertTrue($usBankAccount->isDefault());

        $usBankAccount = Braintree\UsBankAccount::factory(['default' => false]);
        $this->assertFalse($usBankAccount->isDefault());
    }
}
