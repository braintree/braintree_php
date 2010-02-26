<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CustomerTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Customer: foo');
        $c = Braintree_Customer::factory(array());
        $c->foo;
    }
}
?>
