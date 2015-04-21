<?php

namespace Test\Unitests;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class PaymentMethodTest extends Setup
{
    public function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree\PaymentMethod::create(array('invalidKey' => 'foo'));
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
            array('billingAddress' => Braintree\AddressGateway::createSignature()),
        );
        $this->assertEquals($expected, Braintree\PaymentMethodGateway::createSignature());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\PaymentMethod::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\PaymentMethod::find('  ');
    }

    public function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\PaymentMethod::find('\t');
    }
}
