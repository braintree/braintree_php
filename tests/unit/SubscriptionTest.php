<?php namespace Braintree\Tests\Unit;

use Braintree\Subscription;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Subscription::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        Subscription::find('\t');
    }
}
