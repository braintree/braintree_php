<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class CreditCardTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree\CreditCard: foo');
        $cc = Braintree\CreditCard::factory(array());
        $cc->foo;
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree\CreditCard::create(array('invalidKey' => 'foo'));
    }

    public function testIsDefault()
    {
        $creditCard = Braintree\CreditCard::factory(array('default' => true));
        $this->assertTrue($creditCard->isDefault());

        $creditCard = Braintree\CreditCard::factory(array('default' => false));
        $this->assertFalse($creditCard->isDefault());
    }

    public function testMaskedNumber()
    {
        $creditCard = Braintree\CreditCard::factory(array('bin' => '123456', 'last4' => '7890'));
        $this->assertEquals('123456******7890', $creditCard->maskedNumber);
    }

    public function testCreateSignature()
    {
        $expected = array(
            'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
            'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
            'deviceData', 'fraudMerchantId', 'paymentMethodNonce',
            array('options' => array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'verificationAmount', 'venmoSdkSession', 'failOnDuplicatePaymentMethod')),
            array(
                'billingAddress' => array(
                    'firstName',
                    'lastName',
                    'company',
                    'countryCodeAlpha2',
                    'countryCodeAlpha3',
                    'countryCodeNumeric',
                    'countryName',
                    'extendedAddress',
                    'locality',
                    'region',
                    'postalCode',
                    'streetAddress',
                ),
            ),
            'customerId'
        );
        $this->assertEquals($expected, Braintree\CreditCardGateway::createSignature());
    }

    public function testUpdateSignature()
    {
        $expected = array(
            'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
            'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
            'deviceData', 'fraudMerchantId', 'paymentMethodNonce',
            array('options' => array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'verificationAmount', 'venmoSdkSession')),
            array(
                'billingAddress' => array(
                    'firstName',
                    'lastName',
                    'company',
                    'countryCodeAlpha2',
                    'countryCodeAlpha3',
                    'countryCodeNumeric',
                    'countryName',
                    'extendedAddress',
                    'locality',
                    'region',
                    'postalCode',
                    'streetAddress',
                    array(
                        'options' => array(
                            'updateExisting'
                        )
                    )
                ),
            ),
        );
        $this->assertEquals($expected, Braintree\CreditCardGateway::updateSignature());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\CreditCard::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\CreditCard::find('  ');
    }

    public function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree\CreditCard::find('\t');
    }

    public function testVerificationIsLatestVerification()
    {
        $creditCard = Braintree\CreditCard::factory(
            array(
                'verifications' => array(
                    array(
                        'id' => '123',
                        'createdAt' => DateTime::createFromFormat('Ymd', '20121212')
                    ),
                    array(
                        'id' => '932',
                        'createdAt' => DateTime::createFromFormat('Ymd', '20121215')
                    ),
                    array(
                        'id' => '456',
                        'createdAt' => DateTime::createFromFormat('Ymd', '20121213')
                    )
                )
            )
        );

        $this->assertEquals('932', $creditCard->verification->id);
    }
}
