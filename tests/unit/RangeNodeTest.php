<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_RangeNodeTest extends PHPUnit_Framework_TestCase
{
    public function testGreaterThanOrEqualTo()
    {
        $node = new Braintree_RangeNode('field');
        $node->greaterThanOrEqualTo('smallest');
        $this->assertEquals(array('min' => 'smallest'), $node->toParam());
    }

    public function testLessThanOrEqualTo()
    {
        $node = new Braintree_RangeNode('field');
        $node->lessThanOrEqualTo('biggest');
        $this->assertEquals(array('max' => 'biggest'), $node->toParam());
    }

    public function testBetween()
    {
        $node = new Braintree_RangeNode('field');
        $node->between('alpha', 'omega');
        $this->assertEquals(array('min' => 'alpha', 'max' => 'omega'), $node->toParam());
    }

    public function testIs()
    {
        $node = new Braintree_RangeNode('field');
        $node->is('something');
        $this->assertEquals(array('is' => 'something'), $node->toParam());
    }
}
