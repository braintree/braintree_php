<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class MultipleValueNodeTest extends Setup
{
    public function testIs()
    {
        $node = new Braintree\MultipleValueNode('field');
        $node->is('value');
        $this->assertEquals(array('value'), $node->toParam());
    }

    public function testIn()
    {
        $node = new Braintree\MultipleValueNode('field');
        $node->in(array('firstValue', 'secondValue'));
        $this->assertEquals(array('firstValue', 'secondValue'), $node->toParam());
    }
}
