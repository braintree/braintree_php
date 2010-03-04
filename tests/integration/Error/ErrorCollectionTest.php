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
}
?>
