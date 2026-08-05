<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class BinDataTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $binData = Braintree\BinData::factory([]);
        $this->assertInstanceOf('Braintree\BinData', $binData);
    }

    public function testFactory_setsAttributes()
    {
        $binData = Braintree\BinData::factory([
            'commercial' => 'Yes',
            'countryOfIssuance' => 'USA',
            'debit' => 'No',
            'durbinRegulated' => 'Yes',
            'healthcare' => 'No',
            'issuingBank' => 'Bank of Test',
            'payroll' => 'Unknown',
            'prepaid' => 'No',
            'productId' => 'F',
        ]);

        $this->assertEquals('Yes', $binData->commercial);
        $this->assertEquals('USA', $binData->countryOfIssuance);
        $this->assertEquals('No', $binData->debit);
        $this->assertEquals('Yes', $binData->durbinRegulated);
        $this->assertEquals('Bank of Test', $binData->issuingBank);
        $this->assertEquals('F', $binData->productId);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $binData = Braintree\BinData::factory(['commercial' => 'Yes']);
        $this->assertStringContainsString('BinData', (string) $binData);
    }
}
