<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class FacilitatorDetailsTest extends Setup
{
    public function testFactory_returnsInstance()
    {
        $details = Braintree\FacilitatorDetails::factory([]);
        $this->assertInstanceOf('Braintree\FacilitatorDetails', $details);
    }

    public function testFactory_setsAttributes()
    {
        $details = Braintree\FacilitatorDetails::factory([
            'oauthApplicationClientId' => 'client_abc',
            'oauthApplicationName' => 'My App',
            'sourcePaymentMethodToken' => 'tok_123',
        ]);

        $this->assertEquals('client_abc', $details->oauthApplicationClientId);
        $this->assertEquals('My App', $details->oauthApplicationName);
        $this->assertEquals('tok_123', $details->sourcePaymentMethodToken);
    }

    public function testToString_returnsClassNameWithAttributes()
    {
        $details = Braintree\FacilitatorDetails::factory(['oauthApplicationName' => 'My App']);
        $this->assertStringContainsString('FacilitatorDetails', (string) $details);
    }
}
