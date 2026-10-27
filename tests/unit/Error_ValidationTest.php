<?php

namespace Test\Unit\Error;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ValidationTest extends Setup
{
    public function testReadsAttributeCodeAndMessage()
    {
        $error = new Braintree\Error\Validation([
            'attribute' => 'amount',
            'code' => '81503',
            'message' => 'Amount is an invalid format.'
        ]);

        $this->assertEquals('amount', $error->attribute);
        $this->assertEquals('81503', $error->code);
        $this->assertEquals('Amount is an invalid format.', $error->message);
    }

    // Extra keys are stored as dynamic properties. On PHP 8.2+ that raises a
    // deprecation, which fails this test, unless the class allows them.
    public function testAcceptsKeysBeyondAttributeCodeAndMessage()
    {
        $error = new Braintree\Error\Validation([
            'attribute' => 'base',
            'code' => '915229',
            'message' => 'Duplicate request.',
            'atMostOnceProcessing' => 'true'
        ]);

        $this->assertEquals('base', $error->attribute);
        $this->assertEquals('915229', $error->code);
        $this->assertEquals('true', $error->atMostOnceProcessing);
    }
}
