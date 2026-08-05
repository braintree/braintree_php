<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class SubscriptionGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->subscription();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\SubscriptionGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function subscriptionResponse()
    {
        return ['subscription' => [
            'id' => 'sub_123',
            'addOns' => [],
            'discounts' => [],
            'transactions' => [],
            'statusHistory' => [],
        ]];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Subscription::create(['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Subscription::update('the_id', ['invalidKey' => 'foo']);
    }

    public function testFind_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected subscription id to be set');
        Braintree\Subscription::find('');
    }

    public function testFind_throwsIfInvalidCharacterId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid subscription id.');
        Braintree\Subscription::find('invalid id!');
    }

    public function testFind_throwsIfWhitespaceId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid subscription id.');
        Braintree\Subscription::find('  ');
    }

    public function testCreate_throwsIfInvalidNestedDescriptorKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: descriptor[invalidKey]');
        Braintree\Subscription::create(['descriptor' => ['invalidKey' => 'foo']]);
    }

    public function testUpdate_throwsIfInvalidNestedOptionsKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: options[invalidKey]');
        Braintree\Subscription::update('the_id', ['options' => ['invalidKey' => 'foo']]);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->subscriptionResponse());
        $result = $gateway->create(['paymentMethodToken' => 'tok_1', 'planId' => 'plan_1']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\Subscription', $result->subscription);
        $this->assertEquals('sub_123', $result->subscription->id);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create(['paymentMethodToken' => 'tok_1', 'planId' => 'plan_1']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected subscription, transaction, or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->create(['paymentMethodToken' => 'tok_1', 'planId' => 'plan_1']);
    }

    public function testUpdate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->subscriptionResponse());
        $result = $gateway->update('sub_123', ['price' => '20.00']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testCancel_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->subscriptionResponse());
        $result = $gateway->cancel('sub_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRetryCharge_withoutAmount()
    {
        $gateway = $this->gatewayWithMock('post', $this->subscriptionResponse());
        $result = $gateway->retryCharge('sub_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRetryCharge_withAmount()
    {
        $mock = $this->createMock('\Braintree\Http');
        $capturedParams = null;
        $mock->method('post')->will($this->returnCallback(function ($path, $params) use (&$capturedParams) {
            $capturedParams = $params;
            return $this->subscriptionResponse();
        }));
        $gateway = Helper::integrationMerchantGateway()->subscription();
        $prop = new \ReflectionProperty('Braintree\SubscriptionGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);

        $gateway->retryCharge('sub_123', '15.00');

        $this->assertEquals('15.00', $capturedParams['transaction']['amount']);
    }

    public function testRetryCharge_withSubmitForSettlement()
    {
        $mock = $this->createMock('\Braintree\Http');
        $capturedParams = null;
        $mock->method('post')->will($this->returnCallback(function ($path, $params) use (&$capturedParams) {
            $capturedParams = $params;
            return $this->subscriptionResponse();
        }));
        $gateway = Helper::integrationMerchantGateway()->subscription();
        $prop = new \ReflectionProperty('Braintree\SubscriptionGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);

        $gateway->retryCharge('sub_123', null, true);

        $this->assertTrue($capturedParams['transaction']['options']['submitForSettlement']);
    }
}
