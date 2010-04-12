<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class Braintree_Error_ValidationErrorCollectionTest extends PHPUnit_Framework_TestCase
{

    function mapValidationErrorsToCodes($validationErrors)
    {
        $codes = array_map(create_function('$validationError', 'return $validationError->code;'), $validationErrors);
        sort($codes);
        return $codes;
    }

    function test_shallowAll_givesAllErrorsShallowly()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => '1234123412341234',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invalid'
                )
            )
        ));

        $this->assertEquals(array(), $result->errors->shallowAll());

        $expectedCustomerErrors = array(Braintree_Error_Codes::$customer['EmailIsInvalid']);
        $actualCustomerErrors = $result->errors->forKey('customer')->shallowAll();
        $this->assertEquals($expectedCustomerErrors, self::mapValidationErrorsToCodes($actualCustomerErrors));

        $expectedCreditCardErrors = array(
            Braintree_Error_Codes::$creditCard['ExpirationDateIsInvalid'],
            Braintree_Error_Codes::$creditCard['NumberIsInvalid']
        );
        $actualCreditCardErrors = $result->errors->forKey('customer')->forKey('creditCard')->shallowAll();
        $this->assertEquals($expectedCreditCardErrors, self::mapValidationErrorsToCodes($actualCreditCardErrors));
    }

    function test_deepAll_givesAllErrorsDeeply()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => '1234123412341234',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invalid'
                )
            )
        ));

        $expectedErrors = array(
            Braintree_Error_Codes::$customer['EmailIsInvalid'],
            Braintree_Error_Codes::$creditCard['ExpirationDateIsInvalid'],
            Braintree_Error_Codes::$creditCard['NumberIsInvalid'],
            Braintree_Error_Codes::$address['CountryNameIsNotAccepted']
        );
        $actualErrors = $result->errors->deepAll();
        $this->assertEquals($expectedErrors, self::mapValidationErrorsToCodes($actualErrors));

        $expectedErrors = array(
            Braintree_Error_Codes::$creditCard['ExpirationDateIsInvalid'],
            Braintree_Error_Codes::$creditCard['NumberIsInvalid'],
            Braintree_Error_Codes::$address['CountryNameIsNotAccepted']
        );
        $actualErrors = $result->errors->forKey('customer')->forKey('creditCard')->deepAll();
        $this->assertEquals($expectedErrors, self::mapValidationErrorsToCodes($actualErrors));
    }
}

?>
