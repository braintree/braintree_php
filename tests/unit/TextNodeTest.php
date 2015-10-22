<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class TextNodeTest extends Setup
{
  public function testIs()
  {
      $node = new Braintree\TextNode('field');
      $node->is('value');
      $this->assertEquals(array('is' => 'value'), $node->toParam());
  }

  public function testIsNot()
  {
      $node = new Braintree\TextNode('field');
      $node->isNot('value');
      $this->assertEquals(array('is_not' => 'value'), $node->toParam());
  }

  public function testStartsWith()
  {
      $node = new Braintree\TextNode('field');
      $node->startsWith('beginning');
      $this->assertEquals(array('starts_with' => 'beginning'), $node->toParam());
  }

  public function testEndsWith()
  {
      $node = new Braintree\TextNode('field');
      $node->endsWith('end');
      $this->assertEquals(array('ends_with' => 'end'), $node->toParam());
  }

  public function testContains()
  {
      $node = new Braintree\TextNode('field');
      $node->contains('middle');
      $this->assertEquals(array('contains' => 'middle'), $node->toParam());
  }
}
