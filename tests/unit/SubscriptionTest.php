<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class SubscriptionTest extends Setup
{
    public function testErrorsOnFindWithBlankArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Subscription::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Subscription::find('\t');
    }
}
