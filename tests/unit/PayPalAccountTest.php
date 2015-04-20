<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_PayPalAccountTest extends PHPUnit_Framework_TestCase
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_PayPalAccount: foo');
        $paypalAccount = Braintree_PayPalAccount::factory(array());
        $paypalAccount->foo;
    }

    public function testIsDefault()
    {
        $paypalAccount = Braintree_PayPalAccount::factory(array('default' => true));
        $this->assertTrue($paypalAccount->isDefault());

        $paypalAccount = Braintree_PayPalAccount::factory(array('default' => false));
        $this->assertFalse($paypalAccount->isDefault());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PayPalAccount::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PayPalAccount::find('  ');
    }

    public function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PayPalAccount::find('\t');
    }
}
