<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;
use PHPUnit\Framework\MockObject\MockObject;

class BankAccountInstantVerificationGatewayTest extends Setup
{
    /** @var MockObject|Braintree\Gateway */
    private $mockGateway;

    /** @var MockObject|Braintree\GraphQLClient */
    private $mockGraphQLClient;

    /** @var Braintree\BankAccountInstantVerificationGateway */
    private $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockGateway = $this->createMock(Braintree\Gateway::class);
        $this->mockGraphQLClient = $this->createMock(Braintree\GraphQLClient::class);

        $mockConfig = $this->createMock(Braintree\Configuration::class);
        $this->mockGateway->config = $mockConfig;
        $this->mockGateway->graphQLClient = $this->mockGraphQLClient;

        $this->gateway = new Braintree\BankAccountInstantVerificationGateway($this->mockGateway);
    }

    public function testCreateJwtSuccess()
    {
        $request = new Braintree\BankAccountInstantVerificationJwtRequest();
        $request->businessName('Test Business')
               ->returnUrl('https://example.com/success')
               ->cancelUrl('https://example.com/cancel');

        $mockResponse = $this->createSuccessfulJwtResponse();

        $this->mockGraphQLClient
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation CreateBankAccountInstantVerificationJwt'),
                $request->toGraphQLVariables()
            )
            ->willReturn($mockResponse);

        $result = $this->gateway->createJwt($request);

        $this->assertTrue($result->success, 'Expected success');
        $this->assertObjectNotHasProperty('errors', $result, 'Expected no errors property on success');
        $this->assertInstanceOf(Braintree\BankAccountInstantVerificationJwt::class, $result->bankAccountInstantVerificationJwt);
        $this->assertEquals('test-jwt-token', $result->bankAccountInstantVerificationJwt->getJwt());
    }

    public function testCreateJwtWithValidationErrors()
    {
        $request = new Braintree\BankAccountInstantVerificationJwtRequest();
        $request->businessName('')
               ->returnUrl('invalid-url');

        $mockResponse = $this->createErrorResponse();

        $this->mockGraphQLClient
            ->expects($this->once())
            ->method('query')
            ->willReturn($mockResponse);

        $result = $this->gateway->createJwt($request);

        $this->assertFalse($result->success, 'Expected failure but got success');
        $this->assertNotNull($result->errors, 'Expected errors but got none');
    }

    /**
     * @dataProvider graphQLVariablesProvider
     */
    public function testToGraphQLVariables($businessName, $returnUrl, $cancelUrl, $expectedInput)
    {
        $request = new Braintree\BankAccountInstantVerificationJwtRequest();
        $request->businessName($businessName)
               ->returnUrl($returnUrl);

        if ($cancelUrl !== null) {
            $request->cancelUrl($cancelUrl);
        }


        $variables = $request->toGraphQLVariables();

        $this->assertNotNull($variables);
        $this->assertArrayHasKey('input', $variables);
        $this->assertEquals($expectedInput, $variables['input']);
    }

    public function graphQLVariablesProvider()
    {
        return [
            'with cancel URL' => [
                'Test Business',
                'https://example.com/return',
                'https://example.com/cancel',
                [
                    'businessName' => 'Test Business',
                    'returnUrl' => 'https://example.com/return',
                    'cancelUrl' => 'https://example.com/cancel'
                ]
            ],
        ];
    }

    private function createSuccessfulJwtResponse()
    {
        return [
            'data' => [
                'createBankAccountInstantVerificationJwt' => [
                    'jwt' => 'test-jwt-token'
                ]
            ]
        ];
    }

    private function createErrorResponse()
    {
        return [
            'errors' => [
                [
                    'message' => 'Validation error',
                    'extensions' => []
                ]
            ]
        ];
    }
}
