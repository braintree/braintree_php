<?php namespace Braintree\Tests\Unit;

use Braintree\Result\Error;

require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    function testCallingNonExsitingFieldReturnsNull()
    {
        $result = new Error(array('errors' => array(), 'params' => array(), 'message' => 'briefly describe'));
        $this->assertNull($result->transaction);
    }
}
