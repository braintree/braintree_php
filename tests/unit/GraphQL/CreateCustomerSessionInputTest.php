<?php

namespace Braintree\Tests\GraphQL\Inputs;

use Braintree\GraphQL\Inputs\CreateCustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use PHPUnit\Framework\TestCase;

class CreateCustomerSessionInputTest extends TestCase
{
    public function testBuilder()
    {

        $customerSessionInput = $this->createTestCustomerSessionInput();

        $createCustomerSessionInput = CreateCustomerSessionInput::builder()
            ->merchantAccountId('merchant-account-id')
            ->sessionId('session-id')
            ->customer($customerSessionInput)
            ->domain('a-domain')
            ->build();


        $this->assertInstanceOf('Braintree\GraphQL\Inputs\CreateCustomerSessionInput', $createCustomerSessionInput);
    }

    public function testToString()
    {
        $customerSessionInput = $this->createTestCustomerSessionInput();

        $createCustomerSessionInput = CreateCustomerSessionInput::builder()
            ->merchantAccountId('merchant-account-id')
            ->sessionId('session-id')
            ->customer($customerSessionInput)
            ->domain('a-domain')
            ->build();


        $expectedString = "Braintree\GraphQL\Inputs\CreateCustomerSessionInput[merchantAccountId=merchant-account-id, sessionId=session-id, customer=Braintree\GraphQL\Inputs\CustomerSessionInput[email=nobody@nowehwere.com, phone=Braintree\GraphQL\Inputs\PhoneInput[countryPhoneCode=1, phoneNumber=5551234567, extensionNumber=1234], deviceFingerprintId=device-fingerprint-id, paypalAppInstalled=1, venmoAppInstalled=], domain=a-domain]";

        $this->assertEquals($expectedString, (string) $createCustomerSessionInput);
    }

    public function testToStringWithNullValues()
    {
        $createCustomerSessionInput = CreateCustomerSessionInput::builder()
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\CreateCustomerSessionInput[]";

        $this->assertEquals($expectedString, (string) $createCustomerSessionInput);
    }

    public function testToArray()
    {
        $customerSessionInput = $this->createTestCustomerSessionInput();

        $createCustomerSessionInput = CreateCustomerSessionInput::builder()
            ->merchantAccountId('merchant-account-id')
            ->sessionId('session-id')
            ->customer($customerSessionInput)
            ->domain('a-domain')
            ->build();

        $expectedArray = [
            'merchantAccountId' => 'merchant-account-id',
            'sessionId' => 'session-id',
            'customer' => [
                'email' => 'nobody@nowehwere.com',
                'phone' => [
                    'countryPhoneCode' => '1',
                    'phoneNumber' => '5551234567',
                    'extensionNumber' => '1234'
                ],
                'deviceFingerprintId' => 'device-fingerprint-id',
                'paypalAppInstalled' => true,
                'venmoAppInstalled' => false,
            ],
            'domain' => 'a-domain'
        ];

        $this->assertEquals($expectedArray, $createCustomerSessionInput->toArray());
    }

    public function testToArrayWithNullValues()
    {
        $createCustomerSessionInput = CreateCustomerSessionInput::builder()
            ->build();

        $expectedArray = [];

        $this->assertEquals($expectedArray, $createCustomerSessionInput->toArray());
    }

    private function createTestCustomerSessionInput()
    {
        $phoneInput = $this->createTestPhoneInput();

        return CustomerSessionInput::builder()
            ->email('nobody@nowehwere.com')
            ->phone($phoneInput)
            ->deviceFingerprintId('device-fingerprint-id')
            ->paypalAppInstalled(true)
            ->venmoAppInstalled(false)
            ->build();
    }

    private function createTestPhoneInput()
    {
        return PhoneInput::builder()
            ->countryPhoneCode('1')
            ->phoneNumber('5551234567')
            ->extensionNumber('1234')
            ->build();
    }
}
