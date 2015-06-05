<?php namespace Braintree\Tests\Unit;

use Braintree\Util;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class UtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Braintree\Exception\Authentication
     */
    function testThrow401Exception()
    {
        Util::throwStatusCodeException(401);
    }

    /**
     * @expectedException \Braintree\Exception\Authorization
     */
    function testThrow403Exception()
    {
        Util::throwStatusCodeException(403);
    }

    /**
     * @expectedException \Braintree\Exception\NotFound
     */
    function testThrow404Exception()
    {
        Util::throwStatusCodeException(404);
    }

    /**
     * @expectedException \Braintree\Exception\UpgradeRequired
     */
    function testThrow426Exception()
    {
        Util::throwStatusCodeException(426);
    }

    /**
     * @expectedException \Braintree\Exception\ServerError
     */
    function testThrow500Exception()
    {
        Util::throwStatusCodeException(500);
    }

    /**
     * @expectedException \Braintree\Exception\DownForMaintenance
     */
    function testThrow503Exception()
    {
        Util::throwStatusCodeException(503);
    }

    /**
     * @expectedException \Braintree\Exception\Unexpected
     */
    function testThrowUnknownException()
    {
        Util::throwStatusCodeException(999);
    }

    function testExtractAttributeAsArrayReturnsEmptyArray()
    {
        $attributes = array();
        $this->assertEquals(array(), Util::extractAttributeAsArray($attributes, "foo"));
    }

    function testDelimeterToUnderscore()
    {
        $this->assertEquals("a_b_c", Util::delimiterToUnderscore("a-b-c"));
    }

    function testCleanClassName()
    {
        $cn = Util::cleanClassName('Transaction');
        $this->assertEquals('transaction', $cn);
    }

    function testimplodeAssociativeArray()
    {
        $array = array(
            'test1' => 'val1',
            'test2' => 'val2',
            'test3' => new \DateTime('2015-05-15 17:21:00'),
        );
        $string = Util::implodeAssociativeArray($array);
        $this->assertEquals('test1=val1, test2=val2, test3=Fri, 15 May 2015 17:21:00 +0000', $string);
    }

    function testVerifyKeys_withThreeLevels()
    {
        $signature = array(
            'firstName',
            array('creditCard' => array('number', array('billingAddress' => array('streetAddress'))))
        );
        $data = array(
            'firstName'  => 'Dan',
            'creditCard' => array(
                'number'         => '5100',
                'billingAddress' => array(
                    'streetAddress' => '1 E Main St'
                )
            )
        );
        Util::verifyKeys($signature, $data);
    }

    function testVerifyKeys_withArrayOfArrays()
    {
        $signature = array(
            array('addOns' => array(array('update' => array('amount', 'existingId'))))
        );

        $goodData = array(
            'addOns' => array(
                'update' => array(
                    array(
                        'amount'     => '50.00',
                        'existingId' => 'increase_10',
                    ),
                    array(
                        'amount'     => '60.00',
                        'existingId' => 'increase_20',
                    )
                )
            )
        );

        Util::verifyKeys($signature, $goodData);

        $badData = array(
            'addOns' => array(
                'update' => array(
                    array(
                        'invalid' => '50.00',
                    )
                )
            )
        );

        $this->setExpectedException('\InvalidArgumentException');
        Util::verifyKeys($signature, $badData);
    }

    function testVerifyKeys_arrayAsValue()
    {
        $signature = array('key');
        $data = array('key' => array('value'));
        $this->setExpectedException('\InvalidArgumentException');
        Util::verifyKeys($signature, $data);
    }

    function testVerifyKeys()
    {
        $signature = array(
            'amount',
            'customerId',
            'orderId',
            'channel',
            'paymentMethodToken',
            'type',
            array(
                'creditCard' =>
                    array('token', 'cvv', 'expirationDate', 'number'),
            ),
            array(
                'customer' =>
                    array(
                        'id',
                        'company',
                        'email',
                        'fax',
                        'firstName',
                        'lastName',
                        'phone',
                        'website'
                    ),
            ),
            array(
                'billing' =>
                    array(
                        'firstName',
                        'lastName',
                        'company',
                        'countryName',
                        'extendedAddress',
                        'locality',
                        'postalCode',
                        'region',
                        'streetAddress'
                    ),
            ),
            array(
                'shipping' =>
                    array(
                        'firstName',
                        'lastName',
                        'company',
                        'countryName',
                        'extendedAddress',
                        'locality',
                        'postalCode',
                        'region',
                        'streetAddress'
                    ),
            ),
            array(
                'options' =>
                    array(
                        'storeInVault',
                        'submitForSettlement',
                        'addBillingAddressToPaymentMethod'
                    ),
            ),
            array(
                'customFields' => array('_anyKey_')
            ),
        );

        // test valid
        $userKeys = array(
            'amount'       => '100.00',
            'customFields' => array(
                'HEY' => 'HO',
                'WAY' => 'NO'
            ),
            'creditCard'   => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
        );

        $n = Util::verifyKeys($signature, $userKeys);
        $this->assertNull($n);

        $userKeys = array(
            'amount'       => '100.00',
            'customFields' => array(
                'HEY' => 'HO',
                'WAY' => 'NO'
            ),
            'bogus'        => 'FAKE',
            'totallyFake'  => 'boom',
            'creditCard'   => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
        );

        // test invalid
        $this->setExpectedException('\InvalidArgumentException');

        Util::verifyKeys($signature, $userKeys);
    }

    /**
     * @expectedException \Braintree\Exception\ValidationsFailed
     */
    function testReturnException()
    {
        $this->success = false;
        Util::returnObjectOrThrowException('Transaction', $this);
    }

    function testReturnObject()
    {
        $this->success = true;
        $this->transaction = new \stdClass();
        $t = Util::returnObjectOrThrowException('Transaction', $this);
        $this->assertInternalType('object', $t);
    }
}
