<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionSearchTest extends PHPUnit_Framework_TestCase
{
    function testSearch_billingCyclesRemaining_isRangeNode()
    {
        $node = Braintree_SubscriptionSearch::billingCyclesRemaining();
        $this->assertType('Braintree_RangeNode', $node);
    }

    function testSearch_price_isRangeNode()
    {
        $node = Braintree_SubscriptionSearch::price();
        $this->assertType('Braintree_RangeNode', $node);
    }

    function testSearch_daysPastDue_isRangeNode()
    {
        $node = Braintree_SubscriptionSearch::daysPastDue();
        $this->assertType('Braintree_RangeNode', $node);
    }

    function testSearch_id_isTextNode()
    {
        $node = Braintree_SubscriptionSearch::id();
        $this->assertType('Braintree_TextNode', $node);
    }

    function testSearch_ids_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::ids();
        $this->assertType('Braintree_MultipleValueNode', $node);
    }

    function testSearch_merchantAccountId_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::merchantAccountId();
        $this->assertType('Braintree_MultipleValueNode', $node);
    }

    function testSearch_status_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::status();
        $this->assertType('Braintree_MultipleValueNode', $node);
    }
}
