<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_UtilTest extends PHPUnit_Framework_TestCase
{

    // test throwStatusCodeException
    /**
     * @expectedException Braintree_Exception_Authentication
     */
    function testThrow401Exception()
    {
        Braintree_Util::throwStatusCodeException(401);
    }
    /**
     * @expectedException Braintree_Exception_Authorization
     */
     function testThrow403Exception()
    {
        Braintree_Util::throwStatusCodeException(403);
    }
    /**
     * @expectedException Braintree_Exception_NotFound
     */
     function testThrow404Exception()
    {
        Braintree_Util::throwStatusCodeException(404);
    }
    /**
     * @expectedException Braintree_Exception_ServerError
     */
     function testThrow500Exception()
    {
        Braintree_Util::throwStatusCodeException(500);
    }
    /**
     * @expectedException Braintree_Exception_DownForMaintenance
     */
     function testThrow503Exception()
    {
        Braintree_Util::throwStatusCodeException(503);
    }
    /**
     * @expectedException Braintree_Exception_Unexpected
     */
     function testThrowUnknownException()
    {
        Braintree_Util::throwStatusCodeException(999);
    }

    function testExtractAttributeAsArrayReturnsEmptyArray()
    {
        $attributes = array();
        $this->assertEquals(array(), Braintree_Util::extractAttributeAsArray($attributes, "foo"));
    }

    function testCleanClassName()
    {
        $cn = Braintree_Util::cleanClassName('Braintree_Transaction');
        $this->assertEquals('transaction', $cn);
    }
    function testimplodeAssociativeArray()
    {
        $array = array('test1' => 'val1',
                       'test2' => 'val2');
        $string = Braintree_Util::implodeAssociativeArray($array);
        $this->assertEquals('test1=val1, test2=val2', $string);
    }
    function testVerifyKeys_withThreeLevels()
    {
        $signature = array(
            'firstName',
            array('creditCard' => array('number', array('billingAddress' => array('streetAddress'))))
        );
        $data = array(
            'firstName' => 'Dan',
            'creditCard' => array(
                'number' => '5100',
                'billingAddress' => array(
                    'streetAddress' => '1 E Main St'
                )
            )
        );
        Braintree_Util::verifyKeys($signature, $data);
    }

    function testVerifyKeys()
    {
        $signature = array(
                'amount', 'customerId', 'orderId', 'paymentMethodToken', 'type',

                array('creditCard'   =>
                    array('token', 'cvv', 'expirationDate', 'number'),
                ),
                array('customer'      =>
                    array(
                        'id', 'company', 'email', 'fax', 'firstName',
                        'lastName', 'phone', 'website'),
                ),
                array('billing'       =>
                    array(
                        'firstName', 'lastName', 'company', 'countryName',
                        'extendedAddress', 'locality', 'postalCode', 'region',
                        'streetAddress'),
                ),
                array('shipping'      =>
                    array(
                        'firstName', 'lastName', 'company', 'countryName',
                        'extendedAddress', 'locality', 'postalCode', 'region',
                        'streetAddress'),
                ),
                array('options'       =>
                    array(
                        'storeInVault', 'submitForSettlement',
                        'addBillingAddressToPaymentMethod'),
                ),
                array('customFields' => array('_anyKey_')
                ),
        );

        $userKeys = array(
                'amount' => '100.00',
                'customFields'   => array('HEY' => 'HO',
                                          'WAY' => 'NO'),
                'bogus' => 'FAKE',
                'totallyFake' => 'boom',
                'creditCard' => array(
                    'number' => '5105105105105100',
                    'expirationDate' => '05/12',
                    ),
                );

        // test invalid
        $this->setExpectedException('InvalidArgumentException');

        Braintree_Util::verifyKeys($signature, $userKeys);
        
        // test valid
        $userKeys = array(
                'amount' => '100.00',
                'customFields'   => array('HEY' => 'HO',
                                          'WAY' => 'NO'),
                'creditCard' => array(
                    'number' => '5105105105105100',
                    'expirationDate' => '05/12',
                    ),
                );

        $n = Braintree_Util::verifyKeys($signature, $userKeys);
        $this->assertNull($n);
    }

}
?>
