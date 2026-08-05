<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class TransactionLineItemTest extends Setup
{
    public function testConstants_haveExpectedValues()
    {
        $this->assertEquals('credit', Braintree\TransactionLineItem::CREDIT);
        $this->assertEquals('debit', Braintree\TransactionLineItem::DEBIT);
    }

    public function testConstructor_setsAttributes()
    {
        $item = new Braintree\TransactionLineItem([
            'name' => 'Widget',
            'kind' => Braintree\TransactionLineItem::DEBIT,
            'quantity' => '1',
            'unitAmount' => '10.00',
            'totalAmount' => '10.00',
        ]);

        $this->assertEquals('Widget', $item->name);
        $this->assertEquals(Braintree\TransactionLineItem::DEBIT, $item->kind);
        $this->assertEquals('10.00', $item->unitAmount);
        $this->assertEquals('10.00', $item->totalAmount);
    }

    public function testConstructor_creditKind()
    {
        $item = new Braintree\TransactionLineItem([
            'name' => 'Refund',
            'kind' => Braintree\TransactionLineItem::CREDIT,
            'quantity' => '1',
            'unitAmount' => '5.00',
            'totalAmount' => '5.00',
        ]);

        $this->assertEquals(Braintree\TransactionLineItem::CREDIT, $item->kind);
    }
}
