<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class PayPalAccountGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->paypalAccount();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\PayPalAccountGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function paypalAccountResponse()
    {
        return ['paypalAccount' => ['token' => 'tok_123', 'email' => 'test@example.com', 'subscriptions' => []]];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testFind_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected paypal account id to be set');
        Braintree\PayPalAccount::find('');
    }

    public function testFind_throwsIfInvalidToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid paypal account token.');
        Braintree\PayPalAccount::find('invalid token!');
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\PayPalAccount::update('valid-token', ['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected paypal account id to be set');
        Braintree\PayPalAccount::update('', []);
    }

    public function testDelete_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected paypal account id to be set');
        Braintree\PayPalAccount::delete('');
    }

    public function testDelete_throwsIfInvalidToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid paypal account token.');
        Braintree\PayPalAccount::delete('invalid token!');
    }

    public function testUpdateSignature()
    {
        $expected = ['token', ['options' => ['makeDefault']]];
        $this->assertEquals($expected, Braintree\PayPalAccountGateway::updateSignature());
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->paypalAccount();
    }

    public function testFind_returnsPayPalAccount()
    {
        $gateway = $this->gatewayWithMock('get', $this->paypalAccountResponse());
        $result = $gateway->find('valid-token');
        $this->assertInstanceOf('Braintree\PayPalAccount', $result);
        $this->assertEquals('tok_123', $result->token);
    }

    public function testFind_throwsNotFoundWhenTokenMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('paypal account with token missing-token not found');
        $gateway = Helper::integrationMerchantGateway()->paypalAccount();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\PayPalAccountGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        $gateway->find('missing-token');
    }

    public function testUpdate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->paypalAccountResponse());
        $result = $gateway->update('valid-token', ['token' => 'new-token']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\PayPalAccount', $result->paypalAccount);
    }

    public function testUpdate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->update('valid-token', ['token' => 'new-token']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testUpdate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected paypal account or apiErrorResponse');
        $gateway = $this->gatewayWithMock('put', ['unexpectedKey' => 'value']);
        $gateway->update('valid-token', ['token' => 'new-token']);
    }

    public function testDelete_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->delete('valid-token');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }
}
