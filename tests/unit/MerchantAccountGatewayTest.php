<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class MerchantAccountGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->merchantAccount();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\MerchantAccountGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function merchantAccountResponse()
    {
        return ['merchantAccount' => ['id' => 'ma_123', 'status' => 'active']];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testGatewayCanBeConstructed()
    {
        $gateway = Helper::integrationMerchantGateway()->merchantAccount();
        $this->assertInstanceOf('Braintree\MerchantAccountGateway', $gateway);
    }

    public function testAll_returnsPaginatedCollection()
    {
        $collection = Helper::integrationMerchantGateway()->merchantAccount()->all();
        $this->assertInstanceOf('Braintree\PaginatedCollection', $collection);
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->merchantAccount();
    }

    public function testCreateForCurrency_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->merchantAccountResponse());
        $result = $gateway->createForCurrency(['currency' => 'USD']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\MerchantAccount', $result->merchantAccount);
    }

    public function testCreateForCurrency_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->createForCurrency(['currency' => 'USD']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreateForCurrency_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected merchant account or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->createForCurrency(['currency' => 'USD']);
    }

    public function testCreateForCurrency_handlesWrappedResponse()
    {
        $gateway = $this->gatewayWithMock('post', ['response' => $this->merchantAccountResponse()]);
        $result = $gateway->createForCurrency(['currency' => 'USD']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testFetchMerchantAccounts_returnsPaginatedResult()
    {
        $gateway = $this->gatewayWithMock('get', [
            'merchantAccounts' => [
                'merchantAccount' => [['id' => 'ma_1', 'status' => 'active']],
                'totalItems' => [1],
                'pageSize' => [50],
            ],
        ]);
        $result = $gateway->fetchMerchantAccounts(1);
        $this->assertInstanceOf('Braintree\PaginatedResult', $result);
        $this->assertEquals(1, $result->getTotalItems());
        $this->assertEquals(50, $result->getPageSize());
    }
}
