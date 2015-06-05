<?php namespace Braintree\Tests\Unit;

use Braintree\Transaction;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class BraintreeTest extends \PHPUnit_Framework_TestCase
{
    function testIsset()
    {
        $t = Transaction::factory(array(
            'creditCard'    => array(
                'expirationMonth' => '05',
                'expirationYear'  => '2010',
                'bin'             => '510510',
                'last4'           => '5100'
            ),
            'customer'      => array(),
            'billing'       => array(),
            'descriptor'    => array(),
            'shipping'      => array(),
            'subscription'  => array('billingPeriodStartDate' => '1983-07-12'),
            'statusHistory' => array()
        ));
        $this->assertTrue(isset($t->creditCard));
        $this->assertFalse(empty($t->creditCard));
    }
}
