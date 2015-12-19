<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class CreditCardVerificationTest extends Setup
{
    public function test_createWithSuccessfulResponse()
    {
        $result = Braintree\CreditCardVerification::create([
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '05/2011',
            ],
      ]);
      $this->assertTrue($result->success);
    }

    public function test_createWithUnsuccessfulResponse()
    {
        $result = Braintree\CreditCardVerification::create([
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$failsSandboxVerification['Visa'],
                'expirationDate' => '05/2011',
            ],
        ]);
        $this->assertFalse($result->success);
        $this->assertEquals($result->verification->status, Braintree\Result\CreditCardVerification::PROCESSOR_DECLINED);

        $verification = $result->verification;

        $this->assertEquals($verification->processorResponseCode, '2000');
        $this->assertEquals($verification->processorResponseText, 'Do Not Honor');
    }
}
