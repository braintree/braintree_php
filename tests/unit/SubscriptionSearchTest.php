<?php namespace Braintree\Tests\Unit;

use Braintree\SubscriptionSearch;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class SubscriptionSearchTest extends \PHPUnit_Framework_TestCase
{
    function testSearch_billingCyclesRemaining_isRangeNode()
    {
        $node = SubscriptionSearch::billingCyclesRemaining();
        $this->assertInstanceOf('\Braintree\RangeNode', $node);
    }

    function testSearch_price_isRangeNode()
    {
        $node = SubscriptionSearch::price();
        $this->assertInstanceOf('\Braintree\RangeNode', $node);
    }

    function testSearch_daysPastDue_isRangeNode()
    {
        $node = SubscriptionSearch::daysPastDue();
        $this->assertInstanceOf('\Braintree\RangeNode', $node);
    }

    function testSearch_id_isTextNode()
    {
        $node = SubscriptionSearch::id();
        $this->assertInstanceOf('\Braintree\TextNode', $node);
    }

    function testSearch_ids_isMultipleValueNode()
    {
        $node = SubscriptionSearch::ids();
        $this->assertInstanceOf('\Braintree\MultipleValueNode', $node);
    }

    function testSearch_inTrialPeriod_isMultipleValueNode()
    {
        $node = SubscriptionSearch::inTrialPeriod();
        $this->assertInstanceOf('\Braintree\MultipleValueNode', $node);
    }

    function testSearch_merchantAccountId_isMultipleValueNode()
    {
        $node = SubscriptionSearch::merchantAccountId();
        $this->assertInstanceOf('\Braintree\MultipleValueNode', $node);
    }

    function testSearch_planId_isMultipleValueOrTextNode()
    {
        $node = SubscriptionSearch::planId();
        $this->assertInstanceOf('\Braintree\MultipleValueOrTextNode', $node);
    }

    function testSearch_status_isMultipleValueNode()
    {
        $node = SubscriptionSearch::status();
        $this->assertInstanceOf('\Braintree\MultipleValueNode', $node);
    }
}
