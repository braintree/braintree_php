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
        Braintree\PaymentMethod::create(['invalidKey' => 'foo']);
    }

    public function testCreateSignature()
    {
        $expected = [
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
            ['options' => [
                'failOnDuplicatePaymentMethod',
                'makeDefault',
                'verificationMerchantAccountId',
                'verifyCard',
                'verificationAmount',
            ]],
            ['billingAddress' => Braintree\AddressGateway::createSignature()],
            'customerId'
        ];
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
