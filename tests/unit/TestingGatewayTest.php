<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class TestingGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->testing();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\TestingGateway', '_http');
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function transactionResponse()
    {
        return ['transaction' => ['id' => 'txn_123', 'status' => 'settled']];
    }

    public function testSettle_throwsIfTraversalId()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $gateway = Helper::integrationMerchantGateway()->testing();
        $gateway->settle('../customers/cust_123');
    }

    public function testSettlementPending_throwsIfTraversalId()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $gateway = Helper::integrationMerchantGateway()->testing();
        $gateway->settlementPending('../customers/cust_123');
    }

    public function testSettlementConfirm_throwsIfTraversalId()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $gateway = Helper::integrationMerchantGateway()->testing();
        $gateway->settlementConfirm('../customers/cust_123');
    }

    public function testSettlementDecline_throwsIfTraversalId()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $gateway = Helper::integrationMerchantGateway()->testing();
        $gateway->settlementDecline('../customers/cust_123');
    }

    public function testSettle_returnsTransaction()
    {
        $gateway = $this->gatewayWithMock('put', $this->transactionResponse());
        $result = $gateway->settle('txn_123');
        $this->assertInstanceOf('Braintree\Transaction', $result);
        $this->assertEquals('txn_123', $result->id);
    }
}
