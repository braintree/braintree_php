<?php

namespace Braintree\Tests\GraphQL\Inputs;

use Braintree\GraphQL\Inputs\UpdateCustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use PHPUnit\Framework\TestCase;

class UpdateCustomerSessionInputTest extends TestCase
{
    public function testBuilder()
    {

        $customerSessionInput = $this->createTestCustomerSessionInput();

        $updateCustomerSessionInput = UpdateCustomerSessionInput::builder('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();


        $this->assertInstanceOf('Braintree\GraphQL\Inputs\UpdateCustomerSessionInput', $updateCustomerSessionInput);
    }

    public function testToString()
    {
        $customerSessionInput = $this->createTestCustomerSessionInput();

        $updateCustomerSessionInput = UpdateCustomerSessionInput::builder('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();


        $expectedString = "Braintree\GraphQL\Inputs\UpdateCustomerSessionInput[merchantAccountId=merchant-account-id, sessionId=session-id, customer=Braintree\GraphQL\Inputs\CustomerSessionInput[email=nobody@nowehwere.com, phone=Braintree\GraphQL\Inputs\PhoneInput[countryPhoneCode=1, phoneNumber=5551234567, extensionNumber=1234], deviceFingerprintId=device-fingerprint-id, paypalAppInstalled=1, venmoAppInstalled=]]";

        $this->assertEquals($expectedString, (string) $updateCustomerSessionInput);
    }

    public function testToArray()
    {
        $customerSessionInput = $this->createTestCustomerSessionInput();

        $updateCustomerSessionInput = UpdateCustomerSessionInput::builder('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
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
            ]
        ];

        $this->assertEquals($expectedArray, $updateCustomerSessionInput->toArray());
    }

    public function testToArrayWithNullValues()
    {
        $updateCustomerSessionInput = UpdateCustomerSessionInput::builder('session-id')
            ->build();

        $expectedArray = ['sessionId' => 'session-id'];

        $this->assertEquals($expectedArray, $updateCustomerSessionInput->toArray());
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
