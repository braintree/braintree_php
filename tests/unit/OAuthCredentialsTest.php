<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class OAuthCredentialsTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $credentials = Braintree\OAuthCredentials::factory([]);
        $this->assertInstanceOf('Braintree\OAuthCredentials', $credentials);
    }

    public function testFactory_setsAttributes()
    {
        $credentials = Braintree\OAuthCredentials::factory([
            'accessToken' => 'access_abc',
            'expiresAt' => '2030-01-01T00:00:00Z',
            'refreshToken' => 'refresh_xyz',
            'tokenType' => 'bearer',
        ]);

        $this->assertEquals('access_abc', $credentials->accessToken);
        $this->assertEquals('2030-01-01T00:00:00Z', $credentials->expiresAt);
        $this->assertEquals('refresh_xyz', $credentials->refreshToken);
        $this->assertEquals('bearer', $credentials->tokenType);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $credentials = Braintree\OAuthCredentials::factory(['tokenType' => 'bearer']);
        $this->assertStringContainsString('OAuthCredentials', (string) $credentials);
    }
}
