<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class CreditCardVerificationTest extends Setup
{
	public function test_createWithInvalidArguments()
	{
        $this->setExpectedException('InvalidArgumentException');
		Braintree\CreditCardVerification::create(['amount' => '123.45', 'invalidProperty' => 'foo']);
	}
}
