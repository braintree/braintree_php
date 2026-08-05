<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class SettlementBatchSummaryTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $summary = Braintree\SettlementBatchSummary::factory(['records' => []]);
        $this->assertInstanceOf('Braintree\SettlementBatchSummary', $summary);
    }

    public function testRecords_returnsRecordsArray()
    {
        $records = [
            ['merchantAccountId' => 'account_1', 'amountSettled' => '100.00'],
            ['merchantAccountId' => 'account_2', 'amountSettled' => '200.00'],
        ];

        $summary = Braintree\SettlementBatchSummary::factory(['records' => $records]);

        $this->assertEquals($records, $summary->records());
    }

    public function testRecords_returnsEmptyArray()
    {
        $summary = Braintree\SettlementBatchSummary::factory(['records' => []]);
        $this->assertEquals([], $summary->records());
    }

    public function testFactory_setsAttributes()
    {
        $summary = Braintree\SettlementBatchSummary::factory([
            'records' => [],
            'settlementDate' => '2024-01-15',
        ]);

        $this->assertEquals('2024-01-15', $summary->settlementDate);
    }
}
