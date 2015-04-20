<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    public function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Subscription::find('');
    }

    public function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Subscription::find('\t');
    }
}
