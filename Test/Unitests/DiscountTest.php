<?php

namespace Test\Unitests;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class DiscountTest extends Setup
{
    public function testFactory()
    {
        $discount = Braintree\Discount::factory(array());

        $this->assertInstanceOf('Braintree\Discount', $discount);
    }
}
