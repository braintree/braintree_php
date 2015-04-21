<?php

namespace Test\Unitests;

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
}
