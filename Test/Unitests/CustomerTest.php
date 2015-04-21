<?php

namespace Test\Unitests;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class CustomerTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree\Customer: foo');
        $c = Braintree\Customer::factory(array());
        $c->foo;
    }

    public function testUpdateSignature_doesNotAlterOptionsInCreditCardUpdateSignature()
    {
        Braintree\CustomerGateway::updateSignature();
        foreach (Braintree\CreditCardGateway::updateSignature() as $key => $value) {
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
        Braintree\Customer::find('');
    }

    public function testFindErrorsOnWhitespaceId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\Customer::find('\t');
    }
}
