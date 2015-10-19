<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use stdClass;
use DateTime;
use Test\Setup;
use Braintree;

class UtilTest extends Setup
{
    /**
     * @expectedException Braintree\Exception\Authentication
     */
    public function testThrow401Exception()
    {
        Braintree\Util::throwStatusCodeException(401);
    }

    /**
     * @expectedException Braintree\Exception\Authorization
     */
    public function testThrow403Exception()
    {
        Braintree\Util::throwStatusCodeException(403);
    }

    /**
     * @expectedException Braintree\Exception\NotFound
     */
    public function testThrow404Exception()
    {
        Braintree\Util::throwStatusCodeException(404);
    }

    /**
     * @expectedException Braintree\Exception\UpgradeRequired
     */
    public function testThrow426Exception()
    {
        Braintree\Util::throwStatusCodeException(426);
    }

    /**
     * @expectedException Braintree\Exception\ServerError
     */
    public function testThrow500Exception()
    {
        Braintree\Util::throwStatusCodeException(500);
    }

    /**
     * @expectedException Braintree\Exception\DownForMaintenance
     */
    public function testThrow503Exception()
    {
        Braintree\Util::throwStatusCodeException(503);
    }

    /**
     * @expectedException Braintree\Exception\Unexpected
     */
    public function testThrowUnknownException()
    {
        Braintree\Util::throwStatusCodeException(999);
    }

    public function testExtractAttributeAsArrayReturnsEmptyArray()
    {
        $attributes = array();
        $this->assertEquals(array(), Braintree\Util::extractAttributeAsArray($attributes, "foo"));
    }

    public function testDelimeterToUnderscore()
    {
        $this->assertEquals("a_b_c", Braintree\Util::delimiterToUnderscore("a-b-c"));
    }

    public function testCleanClassName()
    {
        $cn = Braintree\Util::cleanClassName('Braintree\Transaction');
        $this->assertEquals('transaction', $cn);
    }

    public function testimplodeAssociativeArray()
    {
        $array = array(
            'test1' => 'val1',
            'test2' => 'val2',
            'test3' => new DateTime('2015-05-15 17:21:00'),
        );
        $string = Braintree\Util::implodeAssociativeArray($array);
        $this->assertEquals('test1=val1, test2=val2, test3=Fri, 15 May 2015 17:21:00 +0000', $string);
    }

    public function testVerifyKeys_withThreeLevels()
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
        Braintree\Util::verifyKeys($signature, $data);
    }

	public function testVerifyKeys_withArrayOfArrays()
	{
        $signature = array(
			array('addOns' => array(array('update' => array('amount', 'existingId'))))
		);

		$goodData = array(
            'addOns' => array(
                'update' => array(
                    array(
                        'amount' => '50.00',
                        'existingId' => 'increase_10',
                    ),
                    array(
                        'amount' => '60.00',
                        'existingId' => 'increase_20',
                    )
                )
            )
		);

        Braintree\Util::verifyKeys($signature, $goodData);

		$badData = array(
            'addOns' => array(
                'update' => array(
                    array(
                        'invalid' => '50.00',
                    )
                )
            )
		);

        $this->setExpectedException('InvalidArgumentException');
        Braintree\Util::verifyKeys($signature, $badData);
	}

    public function testVerifyKeys_arrayAsValue()
    {
        $signature = array('key');
        $data = array('key' => array('value'));
        $this->setExpectedException('InvalidArgumentException');
        Braintree\Util::verifyKeys($signature, $data);
    }

    public function testVerifyKeys()
    {
        $signature = array(
                'amount', 'customerId', 'orderId', 'channel', 'paymentMethodToken', 'type',

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

        $n = Braintree\Util::verifyKeys($signature, $userKeys);
        $this->assertNull($n);

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

        Braintree\Util::verifyKeys($signature, $userKeys);
    }

    /**
     * @expectedException Braintree\Exception\ValidationsFailed
     */
    public function testReturnException()
    {
        $this->success = false;
        Braintree\Util::returnObjectOrThrowException('Braintree\Transaction', $this);
    }

    public function testReturnObject()
    {
        $this->success = true;
        $this->transaction = new stdClass();
        $t = Braintree\Util::returnObjectOrThrowException('Braintree\Transaction', $this);
        $this->assertInternalType('object', $t);
    }
}
