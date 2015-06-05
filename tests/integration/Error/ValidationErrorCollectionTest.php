<?php namespace Braintree\Tests\Integration;

use Braintree\Customer;
use Braintree\Error\Codes;

require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class ValidationErrorCollectionTest extends \PHPUnit_Framework_TestCase
{

    function mapValidationErrorsToCodes($validationErrors)
    {
        $codes = array_map(create_function('$validationError', 'return $validationError->code;'), $validationErrors);
        sort($codes);
        return $codes;
    }

    function test_shallowAll_givesAllErrorsShallowly()
    {
        $result = Customer::create(array(
            'email'      => 'invalid',
            'creditCard' => array(
                'number'         => '1234123412341234',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invalid'
                )
            )
        ));

        $this->assertEquals(array(), $result->errors->shallowAll());

        $expectedCustomerErrors = array(Codes::CUSTOMER_EMAIL_IS_INVALID);
        $actualCustomerErrors = $result->errors->forKey('customer')->shallowAll();
        $this->assertEquals($expectedCustomerErrors, self::mapValidationErrorsToCodes($actualCustomerErrors));

        $expectedCreditCardErrors = array(
            Codes::CREDIT_CARD_EXPIRATION_DATE_IS_INVALID,
            Codes::CREDIT_CARD_NUMBER_IS_INVALID
        );
        $actualCreditCardErrors = $result->errors->forKey('customer')->forKey('creditCard')->shallowAll();
        $this->assertEquals($expectedCreditCardErrors, self::mapValidationErrorsToCodes($actualCreditCardErrors));
    }

    function test_deepAll_givesAllErrorsDeeply()
    {
        $result = Customer::create(array(
            'email'      => 'invalid',
            'creditCard' => array(
                'number'         => '1234123412341234',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invalid'
                )
            )
        ));

        $expectedErrors = array(
            Codes::CUSTOMER_EMAIL_IS_INVALID,
            Codes::CREDIT_CARD_EXPIRATION_DATE_IS_INVALID,
            Codes::CREDIT_CARD_NUMBER_IS_INVALID,
            Codes::ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED
        );
        $actualErrors = $result->errors->deepAll();
        $this->assertEquals($expectedErrors, self::mapValidationErrorsToCodes($actualErrors));

        $expectedErrors = array(
            Codes::CREDIT_CARD_EXPIRATION_DATE_IS_INVALID,
            Codes::CREDIT_CARD_NUMBER_IS_INVALID,
            Codes::ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED
        );
        $actualErrors = $result->errors->forKey('customer')->forKey('creditCard')->deepAll();
        $this->assertEquals($expectedErrors, self::mapValidationErrorsToCodes($actualErrors));
    }
}
