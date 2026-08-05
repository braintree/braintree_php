<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class AddOnGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->addOn();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\AddOnGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    public function testGatewayCanBeConstructed()
    {
        $gateway = Helper::integrationMerchantGateway()->addOn();
        $this->assertInstanceOf('Braintree\AddOnGateway', $gateway);
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->addOn();
    }

    public function testAll_returnsArrayOfAddOns()
    {
        $gateway = $this->gatewayWithMock('get', [
            'addOns' => [
                ['id' => 'addon_1', 'amount' => '5.00', 'name' => 'Test AddOn', 'addOns' => [], 'discounts' => []],
                ['id' => 'addon_2', 'amount' => '10.00', 'name' => 'Other AddOn', 'addOns' => [], 'discounts' => []],
            ],
        ]);
        $result = $gateway->all();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf('Braintree\AddOn', $result[0]);
        $this->assertEquals('addon_1', $result[0]->id);
    }

    public function testAll_returnsEmptyArrayWhenNoAddOns()
    {
        $gateway = $this->gatewayWithMock('get', ['addOns' => []]);
        $result = $gateway->all();
        $this->assertEquals([], $result);
    }
}
