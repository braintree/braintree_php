<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PayPalAccountTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->expectException('PHPUnit\Framework\Error\Error', 'Undefined property on Braintree\PayPalAccount: foo');
        $paypalAccount = Braintree\PayPalAccount::factory([]);
        $paypalAccount->foo;
    }

    public function testIsDefault()
    {
        $paypalAccount = Braintree\PayPalAccount::factory(['default' => true]);
        $this->assertTrue($paypalAccount->isDefault());

        $paypalAccount = Braintree\PayPalAccount::factory(['default' => false]);
        $this->assertFalse($paypalAccount->isDefault());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\PayPalAccount::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\PayPalAccount::find('  ');
    }

    public function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\PayPalAccount::find('\t');
    }
}
