<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransactionTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $t = Braintree_Transaction::factory(array(
            'creditCard' => array(),
            'customer' => array(),
            'billing' => array(),
            'shipping' => array(),
            'statusHistory' => array()
        ));
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Transaction: foo');
        $t->foo;
    }

    function test_gatewayRejectionReason()
    {
        $t = Braintree_Transaction::factory(array(
            'creditCard' => array(),
            'customer' => array(),
            'billing' => array(),
            'shipping' => array(),
            'statusHistory' => array(),
            'gatewayRejectionReason' => Braintree_Transaction::AVS
        ));

        $this->assertEquals(Braintree_Transaction::AVS, $t->gatewayRejectionReason);
    }
}
?>
