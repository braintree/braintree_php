<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_OAuthGatewayTest extends PHPUnit_Framework_TestCase
{
    /**
    * @expectedException Braintree_Exception_Configuration
    * @expectedExceptionMessage clientId needs to be set.
    */
    function testConfigGetsAssertedValid()
    {
        new Braintree_OAuthGateway(array());
    }
}
