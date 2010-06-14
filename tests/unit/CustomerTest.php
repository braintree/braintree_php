<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CustomerTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Customer: foo');
        $c = Braintree_Customer::factory(array());
        $c->foo;
    }

    function testUpdateSignature_doesNotAlterOptionsInCreditCardUpdateSignature()
    {
        Braintree_Customer::updateSignature();
        foreach(Braintree_CreditCard::updateSignature() AS $key => $value) {
            if(is_array($value) and array_key_exists('options', $value)) {
                $this->assertEquals(array(
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard'
                ), $value['options']);
            }
        }
    }
}
?>
