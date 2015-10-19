<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class DisbursementDetailsTest extends Setup
{
    public function testIsValidReturnsTrue()
    {
        $details = new Braintree\DisbursementDetails(array(
            "disbursementDate" => new DateTime("2013-04-10")
        ));

        $this->assertTrue($details->isValid());
    }

    public function testIsValidReturnsFalse()
    {
        $details = new Braintree\DisbursementDetails(array(
            "disbursementDate" => null
        ));

        $this->assertFalse($details->isValid());
    }
}
