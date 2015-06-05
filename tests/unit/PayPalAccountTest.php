<?php namespace Braintree\Tests\Unit;

use Braintree\PayPalAccount;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class PayPalAccountTest extends \PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree\PayPalAccount: foo');
        $paypalAccount = PayPalAccount::factory(array());
        $paypalAccount->foo;
    }

    function testIsDefault()
    {
        $paypalAccount = PayPalAccount::factory(array('default' => true));
        $this->assertTrue($paypalAccount->isDefault());

        $paypalAccount = PayPalAccount::factory(array('default' => false));
        $this->assertFalse($paypalAccount->isDefault());
    }

    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        PayPalAccount::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        PayPalAccount::find('  ');
    }

    function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        PayPalAccount::find('\t');
    }
}
