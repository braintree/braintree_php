<?php namespace Braintree\Tests\Unit;

use Braintree\ClientToken;

require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class ClientTokenTest extends \PHPUnit_Framework_TestCase
{
    function testErrorsWhenCreditCardOptionsGivenWithoutCustomerId()
    {
        $this->setExpectedException('\InvalidArgumentException', 'invalid keys: options[makeDefault]');
        ClientToken::generate(array("options" => array("makeDefault" => true)));
    }

    function testErrorsWhenInvalidArgumentIsSupplied()
    {
        $this->setExpectedException('\InvalidArgumentException', 'invalid keys: customrId');
        ClientToken::generate(array("customrId" => "1234"));
    }
}
