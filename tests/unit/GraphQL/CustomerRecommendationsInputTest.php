<?php

namespace Test\Unit\GraphQL;

use Braintree\GraphQL\Inputs\CustomerRecommendationsInput;
use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\MonetaryAmountInput;
use Braintree\GraphQL\Inputs\PayPalPayeeInput;
use Braintree\GraphQL\Inputs\PayPalPurchaseUnitInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use Braintree\GraphQL\Enums\Recommendations;
use PHPUnit\Framework\TestCase;

class CustomerRecommendationsInputTest extends TestCase
{
    public function testBuilder()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $payee = PayPalPayeeInput::builder()
            ->emailAddress('test@example.com')
            ->clientId('merchant-public-id')
            ->build();

        $amount = MonetaryAmountInput::factory(['value' => '300.00', 'currencyCode' => 'USD']);

        $purchaseUnit = PayPalPurchaseUnitInput::builder($amount)
            ->payee($payee)
            ->build();

        $customerRecommendationsInput = CustomerRecommendationsInput::builder()
            ->sessionId('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->purchaseUnits([$purchaseUnit])
            ->build();


        $this->assertInstanceOf('Braintree\GraphQL\Inputs\CustomerRecommendationsInput', $customerRecommendationsInput);
    }

    public function testToString()
    {

        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $payee = PayPalPayeeInput::builder()
            ->emailAddress('test@example.com')
            ->clientId('merchant-public-id')
            ->build();

        $amount = MonetaryAmountInput::factory(['value' => '300.00', 'currencyCode' => 'USD']);

        $purchaseUnit = PayPalPurchaseUnitInput::builder($amount)
            ->payee($payee)
            ->build();

        $customerRecommendationsInput = CustomerRecommendationsInput::builder()
            ->sessionId('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->purchaseUnits([$purchaseUnit])
            ->domain('a-domain')
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\CustomerRecommendationsInput[sessionId=session-id, customer=Braintree\GraphQL\Inputs\CustomerSessionInput[], purchaseUnits=[Braintree\GraphQL\Inputs\PayPalPurchaseUnitInput[payee=Braintree\GraphQL\Inputs\PayPalPayeeInput[emailAddress=test@example.com, clientId=merchant-public-id], amount=Braintree\GraphQL\Inputs\MonetaryAmountInput[value=300.00, currencyCode=USD]]], domain=a-domain, merchantAccountId=merchant-account-id]";

        $this->assertEquals($expectedString, (string) $customerRecommendationsInput);
    }

    public function testToArray()
    {

        $customerSessionInput = $this->createTestCustomerSessionInput();

        $payee = PayPalPayeeInput::builder()
            ->emailAddress('test@example.com')
            ->clientId('merchant-public-id')
            ->build();

        $amount = MonetaryAmountInput::factory(['value' => '300.00', 'currencyCode' => 'USD']);

        $purchaseUnit = PayPalPurchaseUnitInput::builder($amount)
            ->payee($payee)
            ->build();

        $customerRecommendationsInput = CustomerRecommendationsInput::builder()
            ->sessionId('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->purchaseUnits([$purchaseUnit])
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
            'purchaseUnits' => [
                [
                    'payee' => [
                        'emailAddress' => 'test@example.com',
                        'clientId' => 'merchant-public-id',
                    ],
                    'amount' => [
                        'value' => '300.00',
                        'currencyCode' => 'USD',
                    ]
                ],
            ],
            'domain' => 'a-domain',
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
