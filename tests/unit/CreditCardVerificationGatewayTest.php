<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class CreditCardVerificationGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->creditCardVerification();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\CreditCardVerificationGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function verificationResponse()
    {
        return ['verification' => ['status' => 'verified', 'creditCard' => []]];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testGatewayCanBeConstructed()
    {
        $gateway = Helper::integrationMerchantGateway()->creditCardVerification();
        $this->assertInstanceOf('Braintree\CreditCardVerificationGateway', $gateway);
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->creditCardVerification();
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->verificationResponse());
        $result = $gateway->create(['creditCard' => ['number' => '4111111111111111', 'expirationDate' => '01/25']]);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\CreditCardVerification', $result->verification);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create(['creditCard' => ['number' => '4111111111111111', 'expirationDate' => '01/25']]);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected transaction or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->create(['creditCard' => ['number' => '4111111111111111', 'expirationDate' => '01/25']]);
    }
}
