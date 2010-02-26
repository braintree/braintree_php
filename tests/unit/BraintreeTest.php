<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_BraintreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Braintree_Exception_ValidationsFailed
     */
    function testReturnException()
    {
        $this->success = false;
        Braintree::returnObjectOrThrowException('Braintree_Transaction', $this);
    }

    function testReturnObject()
    {
        $this->success = true;
        $this->transaction = new stdClass();
        $t = Braintree::returnObjectOrThrowException('Braintree_Transaction', $this);
        $this->assertType('object', $t);
    }

}
?>
