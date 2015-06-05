<?php namespace Braintree\Tests\Unit;

use Braintree\TextNode;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class TextNodeTest extends \PHPUnit_Framework_TestCase
{
    function testIs()
    {
        $node = new TextNode('field');
        $node->is('value');
        $this->assertEquals(array('is' => 'value'), $node->toParam());
    }

    function testIsNot()
    {
        $node = new TextNode('field');
        $node->isNot('value');
        $this->assertEquals(array('is_not' => 'value'), $node->toParam());
    }

    function testStartsWith()
    {
        $node = new TextNode('field');
        $node->startsWith('beginning');
        $this->assertEquals(array('starts_with' => 'beginning'), $node->toParam());
    }

    function testEndsWith()
    {
        $node = new TextNode('field');
        $node->endsWith('end');
        $this->assertEquals(array('ends_with' => 'end'), $node->toParam());
    }

    function testContains()
    {
        $node = new TextNode('field');
        $node->contains('middle');
        $this->assertEquals(array('contains' => 'middle'), $node->toParam());
    }
}
