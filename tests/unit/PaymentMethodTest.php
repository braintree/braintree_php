<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_PaymentMethodTest extends PHPUnit_Framework_TestCase
{
    public function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree_PaymentMethod::create(array('invalidKey' => 'foo'));
    }

    public function testCreateSignature()
    {
        $expected = array(
            'customerId', 'paymentMethodNonce', 'token', 'billingAddressId', 'deviceData',
            array('options' => array(
                'makeDefault',
                'verifyCard',
                'failOnDuplicatePaymentMethod',
                'verificationMerchantAccountId',
            )),
            array('billingAddress' => Braintree_AddressGateway::createSignature()),
        );
        $this->assertEquals($expected, Braintree_PaymentMethodGateway::createSignature());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('  ');
    }

    public function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('\t');
    }
}
