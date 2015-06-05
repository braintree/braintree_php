<?php namespace Braintree\Tests\Unit;

use Braintree\CreditCard;
use Braintree\CreditCardGateway;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class CreditCardTest extends \PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree\CreditCard: foo');
        $cc = CreditCard::factory(array());
        $cc->foo;
    }

    function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('\InvalidArgumentException', 'invalid keys: invalidKey');
        CreditCard::create(array('invalidKey' => 'foo'));
    }

    function testIsDefault()
    {
        $creditCard = CreditCard::factory(array('default' => true));
        $this->assertTrue($creditCard->isDefault());

        $creditCard = CreditCard::factory(array('default' => false));
        $this->assertFalse($creditCard->isDefault());
    }

    function testMaskedNumber()
    {
        $creditCard = CreditCard::factory(array('bin' => '123456', 'last4' => '7890'));
        $this->assertEquals('123456******7890', $creditCard->maskedNumber);
    }

    function testCreateSignature()
    {
        $expected = array(
            'billingAddressId',
            'cardholderName',
            'cvv',
            'number',
            'deviceSessionId',
            'expirationDate',
            'expirationMonth',
            'expirationYear',
            'token',
            'venmoSdkPaymentMethodCode',
            'deviceData',
            'fraudMerchantId',
            'paymentMethodNonce',
            array(
                'options' => array(
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard',
                    'verificationAmount',
                    'venmoSdkSession',
                    'failOnDuplicatePaymentMethod'
                )
            ),
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
        $this->assertEquals($expected, CreditCardGateway::createSignature());
    }

    function testUpdateSignature()
    {
        $expected = array(
            'billingAddressId',
            'cardholderName',
            'cvv',
            'number',
            'deviceSessionId',
            'expirationDate',
            'expirationMonth',
            'expirationYear',
            'token',
            'venmoSdkPaymentMethodCode',
            'deviceData',
            'fraudMerchantId',
            'paymentMethodNonce',
            array(
                'options' => array(
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard',
                    'verificationAmount',
                    'venmoSdkSession'
                )
            ),
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
        $this->assertEquals($expected, CreditCardGateway::updateSignature());
    }

    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        CreditCard::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        CreditCard::find('  ');
    }

    function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        CreditCard::find('\t');
    }

    function testVerificationIsLatestVerification()
    {
        $creditCard = CreditCard::factory(
            array(
                'verifications' => array(
                    array(
                        'id'        => '123',
                        'createdAt' => \DateTime::createFromFormat('Ymd', '20121212')
                    ),
                    array(
                        'id'        => '932',
                        'createdAt' => \DateTime::createFromFormat('Ymd', '20121215')
                    ),
                    array(
                        'id'        => '456',
                        'createdAt' => \DateTime::createFromFormat('Ymd', '20121213')
                    )
                )
            )
        );

        $this->assertEquals('932', $creditCard->verification->id);
    }
}
