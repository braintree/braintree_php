<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class ApplePayGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->applePay();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\ApplePayGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->applePay();
    }

    public function testCreateSignature_containsExpectedFields()
    {
        $sig = Braintree\ApplePayGateway::createSignature();
        $this->assertContains('cardholderName', $sig);
        $this->assertContains('cryptogram', $sig);
        $this->assertContains('expirationMonth', $sig);
        $this->assertContains('expirationYear', $sig);
        $this->assertContains('number', $sig);
        $this->assertContains('token', $sig);
    }

    public function testCreateSignature_doesNotContainMakeDefault()
    {
        $sig = Braintree\ApplePayGateway::createSignature();
        $optionsSignature = null;
        foreach ($sig as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('options', $value)) {
                $optionsSignature = $value['options'];
            }
        }
        $this->assertNotContains('makeDefault', $optionsSignature);
    }

    public function testUpdateSignature_containsMakeDefault()
    {
        $sig = Braintree\ApplePayGateway::updateSignature();
        $optionsSignature = null;
        foreach ($sig as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('options', $value)) {
                $optionsSignature = $value['options'];
            }
        }
        $this->assertContains('makeDefault', $optionsSignature);
    }

    public function testRegisterDomain_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', [
            'response' => ['success' => true],
        ]);
        $result = $gateway->registerDomain('example.com');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRegisterDomain_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->registerDomain('example.com');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testUnregisterDomain_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->unregisterDomain('example.com');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRegisteredDomains_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('get', [
            'response' => ['domains' => ['example.com'], 'merchantIdentifier' => 'merchant.com.example'],
        ]);
        $result = $gateway->registeredDomains();
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\ApplePayOptions', $result->applePayOptions);
    }

    public function testRegisteredDomains_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('get', $this->errorResponse());
        $result = $gateway->registeredDomains();
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testRegisteredDomains_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('expected response or apiErrorResponse');
        $gateway = $this->gatewayWithMock('get', ['unexpectedKey' => 'value']);
        $gateway->registeredDomains();
    }
}
