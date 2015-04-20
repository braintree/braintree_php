<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_CreditCardTest extends PHPUnit_Framework_TestCase
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_CreditCard: foo');
        $cc = Braintree_CreditCard::factory(array());
        $cc->foo;
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree_CreditCard::create(array('invalidKey' => 'foo'));
    }

    public function testIsDefault()
    {
        $creditCard = Braintree_CreditCard::factory(array('default' => true));
        $this->assertTrue($creditCard->isDefault());

        $creditCard = Braintree_CreditCard::factory(array('default' => false));
        $this->assertFalse($creditCard->isDefault());
    }

    public function testMaskedNumber()
    {
        $creditCard = Braintree_CreditCard::factory(array('bin' => '123456', 'last4' => '7890'));
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
            'customerId',
        );
        $this->assertEquals($expected, Braintree_CreditCardGateway::createSignature());
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
                            'updateExisting',
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, Braintree_CreditCardGateway::updateSignature());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_CreditCard::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_CreditCard::find('  ');
    }

    public function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_CreditCard::find('\t');
    }

    public function testVerificationIsLatestVerification()
    {
        $creditCard = Braintree_CreditCard::factory(
            array(
                'verifications' => array(
                    array(
                        'id' => '123',
                        'createdAt' => DateTime::createFromFormat('Ymd', '20121212'),
                    ),
                    array(
                        'id' => '932',
                        'createdAt' => DateTime::createFromFormat('Ymd', '20121215'),
                    ),
                    array(
                        'id' => '456',
                        'createdAt' => DateTime::createFromFormat('Ymd', '20121213'),
                    ),
                ),
            )
        );

        $this->assertEquals('932', $creditCard->verification->id);
    }
}
