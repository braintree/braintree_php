<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class OAuthResultTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $result = Braintree\OAuthResult::factory([]);
        $this->assertInstanceOf('Braintree\OAuthResult', $result);
    }

    public function testFactory_setsAttributes()
    {
        $result = Braintree\OAuthResult::factory([
            'accessToken' => 'access_abc',
            'expiresAt' => '2030-01-01T00:00:00Z',
            'refreshToken' => 'refresh_xyz',
            'tokenType' => 'bearer',
        ]);

        $this->assertEquals('access_abc', $result->accessToken);
        $this->assertEquals('2030-01-01T00:00:00Z', $result->expiresAt);
        $this->assertEquals('refresh_xyz', $result->refreshToken);
        $this->assertEquals('bearer', $result->tokenType);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $result = Braintree\OAuthResult::factory(['tokenType' => 'bearer']);
        $this->assertStringContainsString('OAuthResult', (string) $result);
    }
}
