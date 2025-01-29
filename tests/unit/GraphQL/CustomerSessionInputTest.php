<?php

namespace Test\Unit\GraphQL;

use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use PHPUnit\Framework\TestCase;

class CustomerSessionInputTest extends TestCase
{
    public function testBuilder()
    {
        $phoneInput =  $this->createTestPhoneInput();

        $customerSessionInput = CustomerSessionInput::builder()
            ->email('nobody@nowehwere.com')
            ->phone($phoneInput)
            ->deviceFingerprintId('device-fingerprint-id"')
            ->paypalAppInstalled(true)
            ->venmoAppInstalled(false)
            ->userAgent("Mozilla")
            ->build();

        $this->assertInstanceOf('Braintree\GraphQL\Inputs\CustomerSessionInput', $customerSessionInput);
    }

    public function testToString()
    {
        $phoneInput = $this->createTestPhoneInput();

        $customerSessionInput = CustomerSessionInput::builder()
            ->email('nobody@nowehwere.com')
            ->phone($phoneInput)
            ->deviceFingerprintId('device-fingerprint-id')
            ->paypalAppInstalled(true)
            ->venmoAppInstalled(false)
            ->userAgent("Mozilla")
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\CustomerSessionInput[email=nobody@nowehwere.com, phone=Braintree\GraphQL\Inputs\PhoneInput[countryPhoneCode=1, phoneNumber=5551234567, extensionNumber=1234], deviceFingerprintId=device-fingerprint-id, paypalAppInstalled=1, venmoAppInstalled=, userAgent=Mozilla]";

        $this->assertEquals($expectedString, (string) $customerSessionInput);
    }


    public function testToStringWithNullValues()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\CustomerSessionInput[]";

        $this->assertEquals($expectedString, (string) $customerSessionInput);
    }

    public function testToArray()
    {
        $phoneInput = $this->createTestPhoneInput();

        $customerSessionInput = CustomerSessionInput::builder()
            ->email('nobody@nowehwere.com')
            ->phone($phoneInput)
            ->deviceFingerprintId('device-fingerprint-id')
            ->paypalAppInstalled(true)
            ->venmoAppInstalled(false)
            ->userAgent("Mozilla")
            ->build();

        $expectedArray = [
            'email' => 'nobody@nowehwere.com',
            'phone' => [
                'countryPhoneCode' => '1',
                'phoneNumber' => '5551234567',
                'extensionNumber' => '1234'
            ],
            'deviceFingerprintId' => 'device-fingerprint-id',
            'paypalAppInstalled' => true,
            'venmoAppInstalled' => false,
            'userAgent' => 'Mozilla',
        ];

        $this->assertEquals($expectedArray, $customerSessionInput->toArray());
    }

    public function testToArrayWithNullValues()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $expectedArray = [];

        $this->assertEquals($expectedArray, $customerSessionInput->toArray());
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
