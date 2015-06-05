<?php namespace Braintree\Tests\Unit;

use Braintree\DisbursementDetails;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class DisbursementDetailsTest extends \PHPUnit_Framework_TestCase
{
    function testIsValidReturnsTrue()
    {
        $details = new DisbursementDetails(array(
            "disbursementDate" => new \DateTime("2013-04-10")
        ));

        $this->assertTrue($details->isValid());
    }

    function testIsValidReturnsFalse()
    {
        $details = new DisbursementDetails(array(
            "disbursementDate" => null
        ));

        $this->assertFalse($details->isValid());
    }
}
