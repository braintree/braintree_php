<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;

use Braintree;
use Braintree\GraphQL\Inputs\CreateCustomerSessionInput;
use Braintree\GraphQL\Inputs\UpdateCustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerRecommendationsInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use Braintree\GraphQL\Enums\Recommendations;
use Braintree\GraphQL\Enums\RecommendedPaymentOption;

class CustomerSessionTest extends Setup
{
    /** @var Braintree\Gateway */
    protected $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'pwpp_multi_account_merchant',
            'publicKey' => 'pwpp_multi_account_merchant_public_key',
            'privateKey' => 'pwpp_multi_account_merchant_private_key',
        ]);
    }


    private function buildCustomerSessionInput($email, $phoneNumber)
    {
        $phoneInput = PhoneInput::builder()
            ->countryPhoneCode("1")
            ->phoneNumber($phoneNumber)
            ->build();

        return CustomerSessionInput::builder()
            ->email($email)
            ->deviceFingerprintId("test")
            ->phone($phoneInput)
            ->paypalAppInstalled(true)
            ->venmoAppInstalled(true)
            ->userAgent("Mozilla")
            ->build();
    }

    private function buildCustomerSession($sessionId)
    {
        $customer = $this->buildCustomerSessionInput(
            "PR1_test@example.com",
            "4085005002"
        );
        $inputBuilder = CreateCustomerSessionInput::builder();

        if ($sessionId) {
            $inputBuilder->sessionId($sessionId);
        }
        $input = $inputBuilder->customer($customer)
            ->build();

        return $this->gateway->customerSession()->createCustomerSession($input);
    }


    public function testCreateCustomerSessionWithoutEmailAndPhone()
    {
        $input = CreateCustomerSessionInput::builder()
            ->merchantAccountId("usd_pwpp_multi_account_merchant_account")
            ->build();

        $result = $this->gateway->customerSession()->createCustomerSession($input);
        $this->assertTrue($result->success);
        $this->assertNotNull($result->sessionId);
    }

    public function testCreateCustomerSessionWithMerchantProvidedSessionId()
    {
        $merchantSessionId = "11EF-A1E7-A5F5EE5C-A2E5-AFD2801469FC";
        $input = CreateCustomerSessionInput::builder()
            ->sessionId($merchantSessionId)
            ->build();

        $result = $this->gateway->customerSession()->createCustomerSession($input);

        $this->assertNotNull($result->sessionId);
        $this->assertEquals($merchantSessionId, $result->sessionId);
    }


    public function testCreateCustomerSessionWithAPIDerivedSessionId()
    {
        $input = CreateCustomerSessionInput::builder()->build();

        $result = $this->gateway->customerSession()->createCustomerSession($input);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->sessionId);
    }


    public function testDoesNotCreateADuplicateCustomerSession()
    {
        $existingSessionId = "11EF-34BC-2702904B-9026-C3ECF4BAC765";

        $result = $this->buildCustomerSession($existingSessionId);

        $this->assertFalse($result->success);
        $this->assertStringContainsString(
            "Session IDs must be unique per merchant",
            $result->errors->deepAll()[0]->message
        );
    }

    public function testUpdatesExistingSession()
    {
        $sessionId = '11EF-A1E7-A5F5EE5C-A2E5-AFD2801469FC';
        $createCustomerSessionInput = CreateCustomerSessionInput::builder()
            ->sessionId($sessionId)
            ->merchantAccountId('usd_pwpp_multi_account_merchant_account')
            ->build();
        $this->gateway->customerSession()->createCustomerSession($createCustomerSessionInput);
        $customer = $this->buildCustomerSessionInput(
            'PR5_test@example.com',
            '4085005005'
        );
        ;
        $updateCustomerSessionInput = UpdateCustomerSessionInput::builder($sessionId)
            ->customer($customer)
            ->build();

        $result = $this->gateway->customerSession()->updateCustomerSession($updateCustomerSessionInput);

        $this->assertEquals($sessionId, $result->sessionId);
    }

    public function testDoesNotUpdateNonExistentSession()
    {


        $sessionId = '11EF-34BC-2702904B-9026-C3ECF4BAC765';
        $customer = $this->buildCustomerSessionInput(
            'PR9_test@example.com',
            '4085005009'
        );
        $updateCustomerSessionInput = UpdateCustomerSessionInput::builder($sessionId)
            ->customer($customer)
            ->build();

        $result = $this->gateway->customerSession()->updateCustomerSession($updateCustomerSessionInput);
        $this->assertFalse($result->success);
        $this->assertStringContainsString(
            "does not exist",
            $result->errors->deepAll()[0]->message
        );
    }

    public function testGetsCustomerRecommendations()
    {
        $customer = $this->buildCustomerSessionInput("PR5_test@example.com", "4085005005");

        $customerRecommendationsInput = CustomerRecommendationsInput::builder('11EF-A1E7-A5F5EE5C-A2E5-AFD2801469FC', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->customer($customer)
            ->build();

        $result = $this->gateway->customerSession()->getCustomerRecommendations($customerRecommendationsInput);

        $this->assertTrue($result->success);
        $payload = $result->customerRecommendations;

        $this->assertTrue($payload->isInPayPalNetwork);

        $paymentOptions = $payload->recommendations->paymentOptions[0];

        $this->assertEquals($paymentOptions->paymentOption, RecommendedPaymentOption::PAYPAL);
        $this->assertEquals($paymentOptions->recommendedPriority, 1);
    }

    public function testDoesNotGetRecommendationsForNonExistentSession()
    {
        $customer = $this->buildCustomerSessionInput("PR9_test@example.com", "4085005009");

        $customerRecommendationsInput = CustomerRecommendationsInput::builder('11EF-A1E7-A5F5EE5C-A2E5-AFD2801469FC', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->customer($customer)
            ->build();

        $result = $this->gateway->customerSession()->getCustomerRecommendations($customerRecommendationsInput);

        $this->assertFalse($result->success);
        $this->assertStringContainsString(
            "does not exist",
            $result->errors->deepAll()[0]->message
        );
    }
}
