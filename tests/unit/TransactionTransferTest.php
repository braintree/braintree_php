<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Braintree;
use Test\Setup;

class TransactionTransferTest extends Setup
{
    private function mockTransactionGatewayDoCreate()
    {
        return $this->getMockBuilder('Braintree\TransactionGateway')
                    ->setConstructorArgs([Braintree\Configuration::gateway()])
                    ->setMethods(['_doCreate'])
                    ->getMock();
    }

    public function testSaleAcceptsTransferType()
    {
        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->once())
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                $this->assertEquals("wallet_transfer", $params["transaction"]["transfer"]["type"]);
            }));

        $transactionParams = [
        'type' => 'sale',
        'amount' => '100.00',
        'transfer' => [
            'type' => "wallet_transfer",
        ],
        ];

        $transactionGateway->sale($transactionParams);
    }
}
