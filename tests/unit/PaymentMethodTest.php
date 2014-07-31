<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_PaymentMethodTest extends PHPUnit_Framework_TestCase
{
    function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree_PaymentMethod::create(array('invalidKey' => 'foo'));
    }

    function testCreateSignature()
    {
        $expected = array(
            'customerId', 'paymentMethodNonce', 'token', 'billingAddressId',
            array('options' => array('makeDefault', 'failOnDuplicatePaymentMethod')),
            array('billingAddress' => Braintree_Address::createSignature())
        );
        $this->assertEquals($expected, Braintree_PaymentMethod::CreateSignature());
    }

    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('  ');
    }

    function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('\t');
    }
}
