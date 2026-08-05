<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class PaymentMethodNonceGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->paymentMethodNonce();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\PaymentMethodNonceGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function nonceResponse()
    {
        return ['paymentMethodNonce' => ['nonce' => 'fake-nonce-123', 'type' => 'PayPalAccount']];
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\PaymentMethodNonce::create('a-payment-method-token', ['invalidKey' => 'foo']);
    }

    public function testCreate_throwsIfInvalidNestedKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: paymentMethodNonce[invalidKey]');
        Braintree\PaymentMethodNonce::create('a-payment-method-token', [
            'paymentMethodNonce' => ['invalidKey' => 'foo'],
        ]);
    }

    public function testCreate_throwsIfInvalidAuthenticationInsightOptionsKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: paymentMethodNonce[authenticationInsightOptions][invalidKey]');
        Braintree\PaymentMethodNonce::create('a-payment-method-token', [
            'paymentMethodNonce' => ['authenticationInsightOptions' => ['invalidKey' => 'foo']],
        ]);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->nonceResponse());
        $result = $gateway->create('a-payment-method-token');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\PaymentMethodNonce', $result->paymentMethodNonce);
        $this->assertEquals('fake-nonce-123', $result->paymentMethodNonce->nonce);
    }

    public function testFind_returnsPaymentMethodNonce()
    {
        $gateway = $this->gatewayWithMock('get', $this->nonceResponse());
        $result = $gateway->find('fake-nonce-123');
        $this->assertInstanceOf('Braintree\PaymentMethodNonce', $result);
        $this->assertEquals('fake-nonce-123', $result->nonce);
    }

    public function testFind_throwsNotFoundWhenNonceMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('payment method nonce with id missing-nonce not found');
        $gateway = Helper::integrationMerchantGateway()->paymentMethodNonce();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\PaymentMethodNonceGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        $gateway->find('missing-nonce');
    }
}
