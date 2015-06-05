<?php namespace Braintree\Tests\Unit;

use Braintree\Discount;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class DiscountTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $discount = Discount::factory(array());

        $this->assertInstanceOf('Braintree\Discount', $discount);
    }
}
