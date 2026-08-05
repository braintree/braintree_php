<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class KeyValueNodeTest extends Setup
{
    public function testConstructor_setsNameAndDefaultsSearchTermToTrue()
    {
        $node = new Braintree\KeyValueNode('refund');
        $this->assertEquals('refund', $node->name);
        $this->assertTrue($node->searchTerm);
    }

    public function testIs_setsSearchTermToGivenValue()
    {
        $node = new Braintree\KeyValueNode('refund');
        $result = $node->is(false);
        $this->assertFalse($node->searchTerm);
    }

    public function testIs_returnsFluentInterface()
    {
        $node = new Braintree\KeyValueNode('refund');
        $result = $node->is(false);
        $this->assertSame($node, $result);
    }

    public function testToParam_returnsCurrentSearchTerm()
    {
        $node = new Braintree\KeyValueNode('refund');
        $this->assertTrue($node->toParam());

        $node->is(false);
        $this->assertFalse($node->toParam());
    }

    public function testToParam_returnsStringValue()
    {
        $node = new Braintree\KeyValueNode('status');
        $node->is('active');
        $this->assertEquals('active', $node->toParam());
    }

    public function testTransactionSearch_refund_isKeyValueNode()
    {
        $node = Braintree\TransactionSearch::refund();
        $this->assertInstanceOf('Braintree\KeyValueNode', $node);
        $this->assertEquals('refund', $node->name);
        $this->assertTrue($node->toParam());
    }
}
