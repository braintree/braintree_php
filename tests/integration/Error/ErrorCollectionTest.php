<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class Braintree_Error_ErrorCollectionTest extends PHPUnit_Framework_TestCase
{
    function testDeepSize_withNestedErrors()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => 'invalid',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invaild'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $this->assertEquals(4, $result->errors->deepSize());
    }

    function testOnHtmlField()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => 'invalid',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invaild'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->onHtmlField('customer[email]');
        $this->assertEquals('81604', $errors[0]->code);
        $errors = $result->errors->onHtmlField('customer[credit_card][number]');
        $this->assertEquals('81716', $errors[0]->code);
        $errors = $result->errors->onHtmlField('customer[credit_card][billing_address][country_name]');
        $this->assertEquals('91803', $errors[0]->code);
    }

    function testOnHtmlField_returnsEmptyArrayIfNone()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'billingAddress' => array(
                    'streetAddress' => '1 E Main St'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->onHtmlField('customer[email]');
        $this->assertEquals('81604', $errors[0]->code);
        $this->assertEquals(array(), $result->errors->onHtmlField('customer[credit_card][number]'));
        $this->assertEquals(array(), $result->errors->onHtmlField('customer[credit_card][billing_address][country_name]'));
    }

    function testOnHtmlField_returnsEmptyForCustomFieldsIfNoErrors()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customFields' => array('storeMe' => 'value')
        ));
        $this->assertEquals(false, $result->success);
        $this->assertEquals(array(), $result->errors->onHtmlField('customer[custom_fields][store_me]'));
    }
}
?>
