<?php namespace Braintree\Tests\Unit;

use Braintree\MultipleValueNode;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class MultipleValueNodeTest extends \PHPUnit_Framework_TestCase
{
    function testIs()
    {
        $node = new MultipleValueNode('field');
        $node->is('value');
        $this->assertEquals(array('value'), $node->toParam());
    }

    function testIn()
    {
        $node = new MultipleValueNode('field');
        $node->in(array('firstValue', 'secondValue'));
        $this->assertEquals(array('firstValue', 'secondValue'), $node->toParam());
    }
}
