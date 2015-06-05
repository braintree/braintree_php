<?php namespace Braintree\Tests\Unit;

use Braintree\Address;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class AddressTest extends \PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('\PHPUnit_Framework_Error', 'Undefined property on Braintree\Address: foo');
        $a = Address::factory(array());
        $a->foo;
    }

    function testIsEqual()
    {
        $first = Address::factory(
            array('customerId' => 'c1', 'id' => 'a1')
        );
        $second = Address::factory(
            array('customerId' => 'c1', 'id' => 'a1')
        );

        $this->assertTrue($first->isEqual($second));
        $this->assertTrue($second->isEqual($first));

    }

    function testIsNotEqual()
    {
        $first = Address::factory(
            array('customerId' => 'c1', 'id' => 'a1')
        );
        $second = Address::factory(
            array('customerId' => 'c1', 'id' => 'not a1')
        );

        $this->assertFalse($first->isEqual($second));
        $this->assertFalse($second->isEqual($first));
    }

    function testCustomerIdNotEqual()
    {
        $first = Address::factory(
            array('customerId' => 'c1', 'id' => 'a1')
        );
        $second = Address::factory(
            array('customerId' => 'not c1', 'id' => 'a1')
        );

        $this->assertFalse($first->isEqual($second));
        $this->assertFalse($second->isEqual($first));
    }

    function testFindErrorsOnBlankCustomerId()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Address::find('', '123');
    }

    function testFindErrorsOnBlankAddressId()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Address::find('123', '');
    }

    function testFindErrorsOnWhitespaceOnlyId()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Address::find('123', '  ');
    }

    function testFindErrorsOnWhitespaceOnlyCustomerId()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Address::find('  ', '123');
    }
}
