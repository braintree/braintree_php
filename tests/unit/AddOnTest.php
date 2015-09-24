<?php
namespace Test\Unit;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class AddOnTest extends Setup
{
    public function testFactory()
    {
        $addOn = Braintree\AddOn::factory(array());

        $this->assertInstanceOf('Braintree\AddOn', $addOn);
    }

    public function testToString()
    {
        $addOn = Braintree\AddOn::factory(array(
            'amount' => '100.00',
            'description' => 'some description',
            'id' => '1',
            'kind' => 'add_on',
            'name' => 'php_add_on',
            'neverExpires' => 'false',
            'numberOfBillingCycles' => '1'
        ));

        $this->assertEquals('Braintree\AddOn[amount=100.00, description=some description, id=1, kind=add_on, name=php_add_on, neverExpires=false, numberOfBillingCycles=1]', (string)$addOn);
    }
}
