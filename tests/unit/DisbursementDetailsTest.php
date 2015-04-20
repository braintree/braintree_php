<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_DisbursementDetailsTest extends PHPUnit_Framework_TestCase
{
    public function testIsValidReturnsTrue()
    {
        $details = new Braintree_DisbursementDetails(array(
            'disbursementDate' => new DateTime('2013-04-10'),
        ));

        $this->assertTrue($details->isValid());
    }

    public function testIsValidReturnsFalse()
    {
        $details = new Braintree_DisbursementDetails(array(
            'disbursementDate' => null,
        ));

        $this->assertFalse($details->isValid());
    }
}
