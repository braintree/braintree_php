<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class PlanGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->plan();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\PlanGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function planResponse()
    {
        return ['plan' => ['id' => 'plan_123', 'name' => 'Test Plan', 'addOns' => [], 'discounts' => []]];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Plan::create(['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Plan::update('the_id', ['invalidKey' => 'foo']);
    }

    public function testFind_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected plan id to be set');
        Braintree\Plan::find('');
    }

    public function testFind_throwsIfInvalidCharacterId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid plan id.');
        Braintree\Plan::find('invalid id!');
    }

    public function testFind_throwsIfWhitespaceId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid plan id.');
        Braintree\Plan::find('  ');
    }

    public function testCreate_throwsIfInvalidNestedAddOnKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: addOns[invalidKey]');
        Braintree\Plan::create(['addOns' => ['invalidKey' => 'foo']]);
    }

    public function testUpdate_throwsIfInvalidNestedDiscountKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: discounts[invalidKey]');
        Braintree\Plan::update('the_id', ['discounts' => ['invalidKey' => 'foo']]);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->planResponse());
        $result = $gateway->create(['name' => 'Test Plan', 'price' => '10.00', 'currencyIsoCode' => 'USD', 'billingFrequency' => 1]);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\Plan', $result->plan);
        $this->assertEquals('plan_123', $result->plan->id);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create(['name' => 'Test Plan', 'price' => '10.00', 'currencyIsoCode' => 'USD', 'billingFrequency' => 1]);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected plan, or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->create(['name' => 'Test Plan', 'price' => '10.00', 'currencyIsoCode' => 'USD', 'billingFrequency' => 1]);
    }

    public function testAll_returnsArrayOfPlans()
    {
        $gateway = $this->gatewayWithMock('get', ['plans' => [['id' => 'plan_1', 'addOns' => [], 'discounts' => []]]]);
        $result = $gateway->all();
        $this->assertIsArray($result);
        $this->assertInstanceOf('Braintree\Plan', $result[0]);
    }

    public function testAll_returnsEmptyArrayWhenNoPlans()
    {
        $gateway = $this->gatewayWithMock('get', []);
        $result = $gateway->all();
        $this->assertEquals([], $result);
    }

    public function testUpdate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->planResponse());
        $result = $gateway->update('plan_123', ['price' => '20.00']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }
}
