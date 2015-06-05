<?php namespace Braintree\Tests\Unit;

use Braintree\AddOn;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class AddOnTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $addOn = AddOn::factory(array());

        $this->assertInstanceOf('Braintree\AddOn', $addOn);
    }
}
