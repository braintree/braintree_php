<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class PaymentMethodGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->paymentMethod();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\PaymentMethodGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function creditCardResponse()
    {
        return ['creditCard' => ['token' => 'tok_123', 'bin' => '411111', 'last4' => '1111']];
    }

    private function nonceResponse()
    {
        return ['paymentMethodNonce' => ['nonce' => 'fake-nonce-123', 'type' => 'PayPalAccount']];
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
        $gateway->paymentMethod();
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\PaymentMethod::create(['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\PaymentMethod::update('valid-token', ['invalidKey' => 'foo']);
    }

    public function testFind_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected payment method id to be set');
        Braintree\PaymentMethod::find('');
    }

    public function testFind_throwsIfInvalidToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid payment method token.');
        Braintree\PaymentMethod::find('invalid token!');
    }

    public function testDelete_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected payment method id to be set');
        Braintree\PaymentMethod::delete('');
    }

    public function testDelete_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\PaymentMethod::delete('valid-token', ['invalidKey' => 'foo']);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->creditCardResponse());
        $result = $gateway->create(['paymentMethodNonce' => 'fake-nonce', 'customerId' => 'cust_123']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\CreditCard', $result->paymentMethod);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create(['paymentMethodNonce' => 'fake-nonce', 'customerId' => 'cust_123']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testFind_returnsPaymentMethod()
    {
        $gateway = $this->gatewayWithMock('get', $this->creditCardResponse());
        $result = $gateway->find('valid-token');
        $this->assertInstanceOf('Braintree\CreditCard', $result);
    }

    public function testFind_throwsNotFoundWhenMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('payment method with token missing-token not found');
        $gateway = Helper::integrationMerchantGateway()->paymentMethod();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\PaymentMethodGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        $gateway->find('missing-token');
    }

    public function testUpdate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->creditCardResponse());
        $result = $gateway->update('valid-token', ['cardholderName' => 'John Smith']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testUpdate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->update('valid-token', ['cardholderName' => 'John Smith']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testDelete_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->delete('valid-token');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testGrant_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->nonceResponse());
        $result = $gateway->grant('shared-token');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\PaymentMethodNonce', $result->paymentMethodNonce);
    }

    public function testGrant_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->grant('shared-token');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testGrant_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected paymentMethodNonce or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->grant('shared-token');
    }

    public function testGrant_acceptsBoolAttribs()
    {
        $gateway = $this->gatewayWithMock('post', $this->nonceResponse());
        $result = $gateway->grant('shared-token', true);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRevoke_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', ['success' => true]);
        $result = $gateway->revoke('shared-token');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRevoke_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->revoke('shared-token');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testRevoke_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected success or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->revoke('shared-token');
    }

    public function testCreateSignature_containsExpectedKeys()
    {
        $sig = Braintree\PaymentMethodGateway::createSignature();
        $this->assertContains('paymentMethodNonce', $sig);
        $this->assertContains('customerId', $sig);
        $this->assertContains('token', $sig);
    }
}
