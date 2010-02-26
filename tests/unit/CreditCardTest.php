<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CreditCardTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_CreditCard: foo');
        $cc = Braintree_CreditCard::factory(array());
        $cc->foo;
    }

    function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree_CreditCard::create(array('invalidKey' => 'foo'));
    }

    function testIsDefault()
    {
        $creditCard = Braintree_CreditCard::factory(array('default' => true));
        $this->assertTrue($creditCard->isDefault());

        $creditCard = Braintree_CreditCard::factory(array('default' => false));
        $this->assertFalse($creditCard->isDefault());
    }

    function testIsExpired()
    {
        $creditCard = Braintree_CreditCard::factory(array('expirationMonth' => '01', 'expirationYear' => '2010'));
        $this->assertTrue($creditCard->isExpired());

        $creditCard = Braintree_CreditCard::factory(array('expirationMonth' => '01', 'expirationYear' => '2009'));
        $this->assertTrue($creditCard->isExpired());

        $creditCard = Braintree_CreditCard::factory(array('expirationMonth' => '01', 'expirationYear' => (date('Y') + 1)));
        $this->assertFalse($creditCard->isExpired());
    }

    function testMaskedNumber()
    {
        $creditCard = Braintree_CreditCard::factory(array('bin' => '123456', 'last4' => '7890'));
        $this->assertEquals('123456******7890', $creditCard->maskedNumber);
    }
}
?>
