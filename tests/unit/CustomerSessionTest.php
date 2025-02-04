<?php

namespace Braintree\Tests;

use Braintree\CustomerSessionGateway;
use Braintree\Exception;
use Braintree\GraphQL\Enums\Recommendations;
use Braintree\GraphQL\Inputs\CreateCustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerRecommendationsInput;
use Braintree\GraphQL\Inputs\CustomerSessionInput;
use Braintree\GraphQL\Inputs\PhoneInput;
use Braintree\GraphQL\Inputs\UpdateCustomerSessionInput;
use Braintree\GraphQL\Types\CustomerRecommendationsPayload;
use Braintree\GraphQL\Types\PaymentOptions;
use Braintree\GraphQL\Unions\CustomerRecommendations;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerSessionTest extends TestCase
{
    /** @var MockObject|GraphQLClient */
    private $mockGraphQLClient;

    /** @var CustomerSessionGateway */
    private $gateway;


    protected function setUp(): void
    {
        $this->mockGraphQLClient = $this->createMock('\Braintree\GraphQLClient');
        $this->gateway = new CustomerSessionGateway($this->mockGraphQLClient);
    }


    public function testCreateCustomerSession_sendsCorrectRequestToGraphQLService()
    {
        $customerSessionInput = $this->createTestCustomerSessionInput();

        $input = CreateCustomerSessionInput::builder()
            ->merchantAccountId('merchant-account-id')
            ->sessionId('session-id')
            ->customer($customerSessionInput)
            ->domain('a-domain')
            ->build();

        $this->mockGraphQLClient->expects($this->once())
            ->method('query')
            ->with(
                CustomerSessionGateway::CREATE_CUSTOMER_SESSION_MUTATION,
                ['input' => $input->toArray()]
            )->willReturn([
                    'data' => [
                        'createCustomerSession' => [
                            'sessionId' => 'returned-session-id'
                        ]
                    ]
                ]);

        $result = $this->gateway->createCustomerSession($input);

        $this->assertInstanceOf(Successful::class, $result);
        $this->assertEquals('returned-session-id', $result->sessionId);
    }


    public function testUpdateCustomerSession_sendsCorrectRequestToGraphQLService()
    {
        $customerSessionInput = $this->createTestCustomerSessionInput();

        $input = UpdateCustomerSessionInput::builder('session-id')
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();

        $this->mockGraphQLClient->expects($this->once())
            ->method('query')
            ->with(
                CustomerSessionGateway::UPDATE_CUSTOMER_SESSION_MUTATION,
                ['input' => $input->toArray()]
            )->willReturn([
                    'data' => [
                        'updateCustomerSession' => [
                            'sessionId' => 'returned-session-id'
                        ]
                    ]
                ]);

        $result = $this->gateway->updateCustomerSession($input);

        $this->assertInstanceOf(Successful::class, $result);
        $this->assertEquals('returned-session-id', $result->sessionId);
    }



    public function testGetCustomerRecommendations_sendsCorrectRequestToGraphQLService()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

        $input = CustomerRecommendationsInput::builder('session-id', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();

        $this->mockGraphQLClient->expects($this->once())
            ->method('query')
            ->with(
                CustomerSessionGateway::GET_CUSTOMER_RECOMMENDATIONS_QUERY,
                ['input' => $input->toArray()]
            )->willReturn([
                    'data' => [
                        'customerRecommendations' => [
                            'isInPayPalNetwork' => true,
                            'recommendations' => [
                                'paymentOptions' => [
                                    [
                                        'paymentOption' => 'paypal',
                                        'recommendedPriority' => 1
                                    ],
                                    [
                                        'paymentOption' => 'venmo',
                                        'recommendedPriority' => 2
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

        $result = $this->gateway->getCustomerRecommendations($input);

        $this->assertInstanceOf(Successful::class, $result);
        $this->assertInstanceOf(CustomerRecommendationsPayload::class, $result->customerRecommendations);
        $this->assertTrue($result->customerRecommendations->isInPayPalNetwork);

        $paymentOptions = $result->customerRecommendations->recommendations->paymentOptions;

        $this->assertEquals(2, count($paymentOptions));

        $this->assertEquals('paypal', $paymentOptions[0]->paymentOption);
        $this->assertEquals(1, $paymentOptions[0]->recommendedPriority);

        $this->assertEquals('venmo', $paymentOptions[1]->paymentOption);
        $this->assertEquals(2, $paymentOptions[1]->recommendedPriority);
    }

    public function testExecuteMutation_returnsErrorIfResponseHasErrors()
    {
        $input = $this->createMock(CreateCustomerSessionInput::class);
        $input->method('toArray')->willReturn([]);
        $this->mockGraphQLClient
            ->method('query')
            ->willThrowException(new Exception\Unexpected("unexpected exception"));
        $this->expectException(Exception\Unexpected::class);
        $this->gateway->createCustomerSession($input);
    }

    public function testGetCustomerRecommendations_returnsErrorIfResponseHasErrors()
    {
        $input = $this->createMock(CustomerRecommendationsInput::class);
        $input->method('toArray')->willReturn([]);

        $this->mockGraphQLClient
            ->method('query')
            ->willThrowException(new Exception\Unexpected("unexpected exception"));

        $this->expectException(Exception\Unexpected::class);
        $this->gateway->getCustomerRecommendations($input);
    }


    public function testExecuteMutation_throwsUnexpectedIfResponseKeyIsMissing()
    {
        $this->expectException(Exception\Unexpected::class);
        $input = $this->createMock(CreateCustomerSessionInput::class);
        $input->method('toArray')->willReturn([]);
        $this->mockGraphQLClient
            ->method('query')
            ->willReturn([
                'data' => []
            ]);
        $this->gateway->createCustomerSession($input);
    }

    public function testExtractCustomerRecommendations_throwsUnexpectedIfResponseKeyIsMissing()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

            $input = CustomerRecommendationsInput::builder('session-id', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();

        $this->expectException(Exception\Unexpected::class);

        $this->mockGraphQLClient->expects($this->once())
            ->method('query')
            ->with(
                CustomerSessionGateway::GET_CUSTOMER_RECOMMENDATIONS_QUERY,
                ['input' => $input->toArray()]
            )->willReturn([
                    'data' => [
                        'customerRecommendationsBad' => [
                            'isInPayPalNetwork' => true,
                            'recommendations' => [
                                'paymentOptions' => [
                                    [
                                        'paymentOption' => 'paypal',
                                        'recommendedPriority' => 1
                                    ],
                                    [
                                        'paymentOption' => 'venmo',
                                        'recommendedPriority' => 2
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

        $this->gateway->getCustomerRecommendations($input);
    }

    public function testGetPaymentOptions_throwsUnexpectedIfResponseKeyIsMissing()
    {
        $customerSessionInput = CustomerSessionInput::builder()
            ->build();

            $input = CustomerRecommendationsInput::builder('session-id', [Recommendations::PAYMENT_RECOMMENDATIONS])
            ->merchantAccountId('merchant-account-id')
            ->customer($customerSessionInput)
            ->build();

        $this->expectException(Exception\Unexpected::class);

        $this->mockGraphQLClient->expects($this->once())
            ->method('query')
            ->with(
                CustomerSessionGateway::GET_CUSTOMER_RECOMMENDATIONS_QUERY,
                ['input' => $input->toArray()]
            )->willReturn([
                    'data' => [
                        'customerRecommendations' => [
                            'isInPayPalNetwork' => true,
                            'recommendations' => [
                                'paymentOptionsBad' => [
                                    [
                                        'paymentOption' => 'paypal',
                                        'recommendedPriority' => 1
                                    ],
                                    [
                                        'paymentOption' => 'venmo',
                                        'recommendedPriority' => 2
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

        $this->gateway->getCustomerRecommendations($input);
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
