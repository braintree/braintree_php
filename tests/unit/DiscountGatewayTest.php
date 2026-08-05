<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class DiscountGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->discount();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\DiscountGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    public function testGatewayCanBeConstructed()
    {
        $gateway = Helper::integrationMerchantGateway()->discount();
        $this->assertInstanceOf('Braintree\DiscountGateway', $gateway);
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->discount();
    }

    public function testAll_returnsArrayOfDiscounts()
    {
        $gateway = $this->gatewayWithMock('get', [
            'discounts' => [
                ['id' => 'disc_1', 'amount' => '5.00', 'name' => 'Test', 'addOns' => [], 'discounts' => []],
                ['id' => 'disc_2', 'amount' => '10.00', 'name' => 'Other', 'addOns' => [], 'discounts' => []],
            ],
        ]);
        $result = $gateway->all();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf('Braintree\Discount', $result[0]);
        $this->assertEquals('disc_1', $result[0]->id);
    }

    public function testAll_returnsEmptyArrayWhenNoDiscounts()
    {
        $gateway = $this->gatewayWithMock('get', ['discounts' => []]);
        $result = $gateway->all();
        $this->assertEquals([], $result);
    }
}
