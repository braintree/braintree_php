<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class LiabilityShiftTest extends Setup
{
    public function testAttributes()
    {
        $liabilityShift = Braintree\LiabilityShift::factory([
            'responsibleParty' => 'paypal',
            'conditions' => [
                'unauthorized',
                'bar'
            ],
        ]);

        $this->assertEquals('paypal', $liabilityShift->responsibleParty);
        $this->assertContains('unauthorized', $liabilityShift->conditions);
        $this->assertContains('bar', $liabilityShift->conditions);
    }
}
