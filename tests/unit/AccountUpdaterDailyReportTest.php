<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class AccountUpdaterDailyReportTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $report = Braintree\AccountUpdaterDailyReport::factory([]);
        $this->assertInstanceOf('Braintree\AccountUpdaterDailyReport', $report);
    }

    public function testFactory_setsAttributes()
    {
        $report = Braintree\AccountUpdaterDailyReport::factory([
            'reportDate' => '2024-01-15',
            'reportUrl' => 'https://example.com/report',
        ]);

        $this->assertEquals('2024-01-15', $report->reportDate);
        $this->assertEquals('https://example.com/report', $report->reportUrl);
    }

    public function testToString_containsReportDate()
    {
        $report = Braintree\AccountUpdaterDailyReport::factory([
            'reportDate' => '2024-01-15',
            'reportUrl' => 'https://example.com/report',
        ]);

        $this->assertStringContainsString('AccountUpdaterDailyReport', (string) $report);
    }

    public function testToString_containsReportUrl()
    {
        $report = Braintree\AccountUpdaterDailyReport::factory([
            'reportDate' => '2024-01-15',
            'reportUrl' => 'https://example.com/report',
        ]);

        $this->assertStringContainsString('https://example.com/report', (string) $report);
    }
}
