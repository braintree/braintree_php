<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CreditCardTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_CreditCard: foo');
        $cc = Braintree_CreditCard::factory(array());
        $cc->foo;
    }

    function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree_CreditCard::create(array('invalidKey' => 'foo'));
    }

    function testIsDefault()
    {
        $creditCard = Braintree_CreditCard::factory(array('default' => true));
        $this->assertTrue($creditCard->isDefault());

        $creditCard = Braintree_CreditCard::factory(array('default' => false));
        $this->assertFalse($creditCard->isDefault());
    }

    function testMaskedNumber()
    {
        $creditCard = Braintree_CreditCard::factory(array('bin' => '123456', 'last4' => '7890'));
        $this->assertEquals('123456******7890', $creditCard->maskedNumber);
    }

    function testCreateSignature()
    {
        $expected = array(
            'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
            'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
            'bundledParams',
            array('options' => array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'venmoSdkSession', 'failOnDuplicatePaymentMethod')),
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
                    'streetAddress'
                ),
            ),
            'customerId'
        );
        $this->assertEquals($expected, Braintree_CreditCard::CreateSignature());
    }

    function testUpdateSignature()
    {
        $expected = array(
            'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
            'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
            'bundledParams',
            array('options' => array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'venmoSdkSession')),
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
        $this->assertEquals($expected, Braintree_CreditCard::UpdateSignature());
    }

    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_CreditCard::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_CreditCard::find('  ');
    }

    function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_CreditCard::find('\t');
    }
}
?>
