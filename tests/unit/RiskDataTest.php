<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class RiskDataTest extends Setup
{

    public function testAttributes()
    {
        $riskData = Braintree\RiskData::factory([
            'customerDeviceId' => 'deviceId',
            'customerLocationZip' => '12345',
            'customerTenure' => 'tenure',
            'decision' => 'decision',
            'id' => 'id',
            'transactionRiskScore' => '100',
            'deviceDataCaptured' => true,
            'decisionReasons' => [
                'foo', 'bar'
            ],
        ]);

        $this->assertEquals('deviceId', $riskData->customerDeviceId);
        $this->assertEquals('12345', $riskData->customerLocationZip);
        $this->assertEquals('tenure', $riskData->customerTenure);
        $this->assertEquals('decision', $riskData->decision);
        $this->assertEquals('id', $riskData->id);
        $this->assertEquals('100', $riskData->transactionRiskScore);
        $this->assertTrue($riskData->deviceDataCaptured);
        $this->assertContains('foo', $riskData->decisionReasons);
        $this->assertContains('bar', $riskData->decisionReasons);
    }
}
