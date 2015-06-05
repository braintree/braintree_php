<?php namespace Braintree\Tests\Unit;

use Braintree\MultipleValueOrTextNode;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class MultipleValueOrTextNodeTest extends \PHPUnit_Framework_TestCase
{
    function testIn()
    {
        $node = new MultipleValueOrTextNode('field');
        $node->in(array('firstValue', 'secondValue'));
        $this->assertEquals(array('firstValue', 'secondValue'), $node->toParam());
    }

    function testIs()
    {
        $node = new MultipleValueOrTextNode('field');
        $node->is('value');
        $this->assertEquals(array('is' => 'value'), $node->toParam());
    }

    function testIsNot()
    {
        $node = new MultipleValueOrTextNode('field');
        $node->isNot('value');
        $this->assertEquals(array('is_not' => 'value'), $node->toParam());
    }

    function testStartsWith()
    {
        $node = new MultipleValueOrTextNode('field');
        $node->startsWith('beginning');
        $this->assertEquals(array('starts_with' => 'beginning'), $node->toParam());
    }

    function testEndsWith()
    {
        $node = new MultipleValueOrTextNode('field');
        $node->endsWith('end');
        $this->assertEquals(array('ends_with' => 'end'), $node->toParam());
    }

    function testContains()
    {
        $node = new MultipleValueOrTextNode('field');
        $node->contains('middle');
        $this->assertEquals(array('contains' => 'middle'), $node->toParam());
    }
}
