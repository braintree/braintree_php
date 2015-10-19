<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

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
            'billingAddressId',
            'cardholderName',
            'cvv',
            'deviceData',
            'expirationDate',
            'expirationMonth',
            'expirationYear',
            'number',
            'paymentMethodNonce',
            'token',
            array('options' => array(
                'failOnDuplicatePaymentMethod',
                'makeDefault',
                'verificationMerchantAccountId',
                'verifyCard'
            )),
            array('billingAddress' => Braintree\AddressGateway::createSignature()),
            'customerId'
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
