<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ThreeDSecureInfoTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $info = Braintree\ThreeDSecureInfo::factory([]);
        $this->assertInstanceOf('Braintree\ThreeDSecureInfo', $info);
    }

    public function testFactory_setsAttributes()
    {
        $info = Braintree\ThreeDSecureInfo::factory([
            'enrolled' => 'Y',
            'liabilityShifted' => true,
            'liabilityShiftPossible' => true,
            'status' => 'authenticate_successful',
            'threeDSecureVersion' => '2.2.0',
            'eciFlag' => '05',
        ]);

        $this->assertEquals('Y', $info->enrolled);
        $this->assertTrue($info->liabilityShifted);
        $this->assertTrue($info->liabilityShiftPossible);
        $this->assertEquals('authenticate_successful', $info->status);
        $this->assertEquals('2.2.0', $info->threeDSecureVersion);
        $this->assertEquals('05', $info->eciFlag);
    }

    public function testFactory_attributeNotSetWhenNotProvided()
    {
        $info = Braintree\ThreeDSecureInfo::factory([]);
        $this->assertFalse(isset($info->enrolled));
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $info = Braintree\ThreeDSecureInfo::factory(['status' => 'authenticate_successful']);
        $this->assertStringContainsString('ThreeDSecureInfo', (string) $info);
    }
}
