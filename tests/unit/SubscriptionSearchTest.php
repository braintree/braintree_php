<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_SubscriptionSearchTest extends PHPUnit_Framework_TestCase
{
    public function testSearch_billingCyclesRemaining_isRangeNode()
    {
        $node = Braintree_SubscriptionSearch::billingCyclesRemaining();
        $this->assertInstanceOf('Braintree_RangeNode', $node);
    }

    public function testSearch_price_isRangeNode()
    {
        $node = Braintree_SubscriptionSearch::price();
        $this->assertInstanceOf('Braintree_RangeNode', $node);
    }

    public function testSearch_daysPastDue_isRangeNode()
    {
        $node = Braintree_SubscriptionSearch::daysPastDue();
        $this->assertInstanceOf('Braintree_RangeNode', $node);
    }

    public function testSearch_id_isTextNode()
    {
        $node = Braintree_SubscriptionSearch::id();
        $this->assertInstanceOf('Braintree_TextNode', $node);
    }

    public function testSearch_ids_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::ids();
        $this->assertInstanceOf('Braintree_MultipleValueNode', $node);
    }

    public function testSearch_inTrialPeriod_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::inTrialPeriod();
        $this->assertInstanceOf('Braintree_MultipleValueNode', $node);
    }

    public function testSearch_merchantAccountId_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::merchantAccountId();
        $this->assertInstanceOf('Braintree_MultipleValueNode', $node);
    }

    public function testSearch_planId_isMultipleValueOrTextNode()
    {
        $node = Braintree_SubscriptionSearch::planId();
        $this->assertInstanceOf('Braintree_MultipleValueOrTextNode', $node);
    }

    public function testSearch_status_isMultipleValueNode()
    {
        $node = Braintree_SubscriptionSearch::status();
        $this->assertInstanceOf('Braintree_MultipleValueNode', $node);
    }
}
