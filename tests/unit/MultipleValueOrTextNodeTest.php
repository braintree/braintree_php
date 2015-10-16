<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class MultipleValueOrTextNodeTest extends Setup
{
    public function testIn()
    {
        $node = new Braintree\MultipleValueOrTextNode('field');
        $node->in(array('firstValue', 'secondValue'));
        $this->assertEquals(array('firstValue', 'secondValue'), $node->toParam());
    }

    public function testIs()
    {
        $node = new Braintree\MultipleValueOrTextNode('field');
        $node->is('value');
        $this->assertEquals(array('is' => 'value'), $node->toParam());
    }

    public function testIsNot()
    {
        $node = new Braintree\MultipleValueOrTextNode('field');
        $node->isNot('value');
        $this->assertEquals(array('is_not' => 'value'), $node->toParam());
    }

    public function testStartsWith()
    {
        $node = new Braintree\MultipleValueOrTextNode('field');
        $node->startsWith('beginning');
        $this->assertEquals(array('starts_with' => 'beginning'), $node->toParam());
    }

    public function testEndsWith()
    {
        $node = new Braintree\MultipleValueOrTextNode('field');
        $node->endsWith('end');
        $this->assertEquals(array('ends_with' => 'end'), $node->toParam());
    }

    public function testContains()
    {
        $node = new Braintree\MultipleValueOrTextNode('field');
        $node->contains('middle');
        $this->assertEquals(array('contains' => 'middle'), $node->toParam());
    }
}
