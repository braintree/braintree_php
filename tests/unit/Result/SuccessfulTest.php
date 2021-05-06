<?php

namespace Test\Unit\Result;

require_once dirname(dirname(__DIR__)) . '/Setup.php';

use Test\Setup;
use Braintree;

class SuccessfulTest extends Setup
{
    public function testCallingNonExsitingFieldReturnsNull()
    {
        $this->expectError();
        $this->expectExceptionMessage('Undefined property on Braintree\Result\Successful: notAProperty');

        $result = new Braintree\Result\Successful(1, 'transaction');

        $this->assertNotNull($result->transaction);
        $this->assertNull($result->notAProperty);
    }
}
