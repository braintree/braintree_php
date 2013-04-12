<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DepositDetailsTest extends PHPUnit_Framework_TestCase
{
    function testIsValidReturnsTrue()
    {
        $details = new Braintree_DepositDetails(array(
            "depositDate" => new DateTime("2013-04-10")
        ));

        $this->assertTrue($details->isValid());
    }

    function testIsValidReturnsFalse()
    {
        $details = new Braintree_DepositDetails(array(
            "depositDate" => null
        ));

        $this->assertFalse($details->isValid());
    }
}
