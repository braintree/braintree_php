<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionSearchTest extends PHPUnit_Framework_TestCase
{
    function testSearch_daysPastDue_is()
    {
        $expected = array("is" => "5");

        $textNode = Braintree_SubscriptionSearch::daysPastDue()->is(5);
        $this->assertEquals($expected, $textNode->toParam());
    }

    function testSearch_daysPastDue_isNot()
    {
        $expected = array("is_not" => "5");

        $textNode = Braintree_SubscriptionSearch::daysPastDue()->isNot(5);
        $this->assertEquals($expected, $textNode->toParam());
    }

    function testSearch_daysPastDue_startsWith()
    {
        $expected = array("starts_with" => "5");

        $textNode = Braintree_SubscriptionSearch::daysPastDue()->startsWith(5);
        $this->assertEquals($expected, $textNode->toParam());
    }

    function testSearch_daysPastDue_contains()
    {
        $expected = array("contains" => "5");

        $textNode = Braintree_SubscriptionSearch::daysPastDue()->contains(5);
        $this->assertEquals($expected, $textNode->toParam());
    }
}

?>
