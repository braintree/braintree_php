<?php

namespace Test\Unit\Test;

require_once dirname(__DIR__, 2) . '/Setup.php';

use Test\Setup;
use Braintree\Test\VenmoSdk;

// NEXT_MAJOR_VERSION Remove this test class when VenmoSdk is removed
class VenmoSdkTest extends Setup
{
    public function testVisaPaymentMethodCode_isStubPrefixed()
    {
        $this->assertStringStartsWith('stub-', VenmoSdk::$visaPaymentMethodCode);
    }

    public function testGenerateTestPaymentMethodCode_prefixesWithStub()
    {
        $code = VenmoSdk::generateTestPaymentMethodCode('4111111111111111');
        $this->assertEquals('stub-4111111111111111', $code);
    }

    public function testGetInvalidPaymentMethodCode_returnsInvalidCode()
    {
        $code = VenmoSdk::getInvalidPaymentMethodCode();
        $this->assertStringContainsString('invalid', $code);
        $this->assertStringStartsWith('stub-', $code);
    }

    public function testGetTestSession_returnsStubSession()
    {
        $session = VenmoSdk::getTestSession();
        $this->assertEquals('stub-session', $session);
    }

    public function testGetInvalidTestSession_returnsInvalidSession()
    {
        $session = VenmoSdk::getInvalidTestSession();
        $this->assertStringContainsString('invalid', $session);
        $this->assertStringStartsWith('stub-', $session);
    }
}
