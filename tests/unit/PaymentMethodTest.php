<?php namespace Braintree\Tests\Unit;

use Braintree\AddressGateway;
use Braintree\PaymentMethod;
use Braintree\PaymentMethodGateway;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class PaymentMethodTest extends \PHPUnit_Framework_TestCase
{
    function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('\InvalidArgumentException', 'invalid keys: invalidKey');
        PaymentMethod::create(array('invalidKey' => 'foo'));
    }

    function testCreateSignature()
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
            array(
                'options' => array(
                    'failOnDuplicatePaymentMethod',
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard'
                )
            ),
            array('billingAddress' => AddressGateway::createSignature()),
            'customerId'
        );
        $this->assertEquals($expected, PaymentMethodGateway::createSignature());
    }

    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        PaymentMethod::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        PaymentMethod::find('  ');
    }

    function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        PaymentMethod::find('\t');
    }
}
