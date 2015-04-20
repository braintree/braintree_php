<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_CustomerTest extends PHPUnit_Framework_TestCase
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Customer: foo');
        $c = Braintree_Customer::factory(array());
        $c->foo;
    }

    public function testUpdateSignature_doesNotAlterOptionsInCreditCardUpdateSignature()
    {
        Braintree_CustomerGateway::updateSignature();
        foreach (Braintree_CreditCardGateway::updateSignature() as $key => $value) {
            if (is_array($value) and array_key_exists('options', $value)) {
                $this->assertEquals(array(
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard',
                    'verificationAmount',
                    'venmoSdkSession',
                ), $value['options']);
            }
        }
    }

    public function testFindErrorsOnBlankId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Customer::find('');
    }

    public function testFindErrorsOnWhitespaceId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Customer::find('\t');
    }
}
