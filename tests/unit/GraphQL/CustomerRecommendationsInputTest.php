<?php

namespace Test\Unit\GraphQL;

use Braintree\GraphQL\Inputs\CustomerRecommendationsInput;
use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use Braintree\GraphQL\Enums\Recommendations;
use PHPUnit\Framework\TestCase;

class CustomerRecommendationsInputTest extends TestCase
{
    public function testBuilder()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $customerRecommendationsInput = CustomerRecommendationsInput::builder('session-id', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();


        $this->assertInstanceOf('Braintree\GraphQL\Inputs\CustomerRecommendationsInput', $customerRecommendationsInput);
    }

    public function testToString()
    {

        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $customerRecommendationsInput = CustomerRecommendationsInput::builder('session-id', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\CustomerRecommendationsInput[merchantAccountId=merchant-account-id, sessionId=session-id, recommendations=[PAYMENT_RECOMMENDATIONS], customer=Braintree\GraphQL\Inputs\CustomerSessionInput[]]";

        $this->assertEquals($expectedString, (string) $customerRecommendationsInput);
    }

    public function testToArray()
    {

        $customerSessionInput = $this->createTestCustomerSessionInput();

        $customerRecommendationsInput = CustomerRecommendationsInput::builder('session-id', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();

        $expectedArray = [
            'merchantAccountId' => 'merchant-account-id',
            'sessionId' => 'session-id',
            'recommendations' => ['PAYMENT_RECOMMENDATIONS'],
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

        $this->assertEquals($expectedArray, $customerRecommendationsInput->toArray());
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
