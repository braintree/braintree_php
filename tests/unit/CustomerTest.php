<?php namespace Braintree\Tests\Unit;

use Braintree\CreditCardGateway;
use Braintree\Customer;
use Braintree\CustomerGateway;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree\Customer: foo');
        $c = Customer::factory(array());
        $c->foo;
    }

    function testUpdateSignature_doesNotAlterOptionsInCreditCardUpdateSignature()
    {
        CustomerGateway::updateSignature();
        foreach (CreditCardGateway::updateSignature() AS $key => $value) {
            if (is_array($value) and array_key_exists('options', $value)) {
                $this->assertEquals(array(
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard',
                    'verificationAmount',
                    'venmoSdkSession'
                ), $value['options']);
            }
        }
    }

    function testCreateSignature_doesNotIncludeCustomerIdOnCreditCard()
    {
        $signature = CustomerGateway::createSignature();
        $creditCardSignatures = array_filter($signature, '\Braintree\Tests\Unit\CustomerTest::findCreditCardArray');
        $creditCardSignature = array_shift($creditCardSignatures)['creditCard'];

        $this->assertNotContains('customerId', $creditCardSignature);
    }

    function findCreditCardArray($el)
    {
        return is_array($el) && array_key_exists('creditCard', $el);
    }

    function testFindErrorsOnBlankId()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Customer::find('');
    }

    function testFindErrorsOnWhitespaceId()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Customer::find('\t');
    }
}
