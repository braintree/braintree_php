<?php namespace Braintree\Tests\Unit;

use Braintree\Result\Successful;

require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class SuccessfulTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Undefined property on Braintree\Result\Successful: notAProperty
     */
    function testCallingNonExsitingFieldReturnsNull()
    {
        $result = new Successful(1, "transaction");
        $this->assertNotNull($result->transaction);
        $this->assertNull($result->notAProperty);
    }
}
