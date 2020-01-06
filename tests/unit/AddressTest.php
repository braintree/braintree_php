<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class AddressTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->expectException('PHPUnit\Framework\Error\Error', 'Undefined property on Braintree\Address: foo');

        $a = Braintree\Address::factory([]);

        $a->foo;
    }

    public function testIsEqual()
    {
        $first = Braintree\Address::factory(
                ['customerId' => 'c1', 'id' => 'a1']
                );
        $second = Braintree\Address::factory(
                ['customerId' => 'c1', 'id' => 'a1']
                );

        $this->assertTrue($first->isEqual($second));
        $this->assertTrue($second->isEqual($first));

    }
    public function testIsNotEqual() {
        $first = Braintree\Address::factory(
                ['customerId' => 'c1', 'id' => 'a1']
                );
        $second = Braintree\Address::factory(
                ['customerId' => 'c1', 'id' => 'not a1']
                );

        $this->assertFalse($first->isEqual($second));
        $this->assertFalse($second->isEqual($first));
    }

    public function testCustomerIdNotEqual()
    {
        $first = Braintree\Address::factory(
                ['customerId' => 'c1', 'id' => 'a1']
                );
        $second = Braintree\Address::factory(
                ['customerId' => 'not c1', 'id' => 'a1']
                );

        $this->assertFalse($first->isEqual($second));
        $this->assertFalse($second->isEqual($first));
    }

    public function testFindErrorsOnBlankCustomerId()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Address::find('', '123');
    }

    public function testFindErrorsOnBlankAddressId()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Address::find('123', '');
    }

    public function testFindErrorsOnWhitespaceOnlyId()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Address::find('123', '  ');
    }

    public function testFindErrorsOnWhitespaceOnlyCustomerId()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Address::find('  ', '123');
    }
}
