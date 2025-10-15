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

    public function testSaleAcceptsTransferTypeOptions()
    {
        $transferType = array("account_to_account", "person_to_person", "wallet_transfer", "boleto_ticket");

        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->exactly(4))
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                isset($params['transaction']['transfer']);
            }));

        foreach ($transferType as $type) {
            $type = $type;
            $transactionParams = [
                'type' => 'sale',
                'amount' => '100.00',
                'transfer' => [
                    'type' => $type,
                    'sender' => [
                        'firstName' => 'Alice',
                        'lastName' => 'Silva',
                        'accountReferenceNumber' => '1000012345',
                        'taxId' => '12345678900',
                        'address' => [
                            'extendedAddress' => '2B',
                            'streetAddress' => 'Rua das Flores, 100',
                            'locality' => 'São Paulo',
                            'region' => 'SP',
                            'countryCodeAlpha2' => 'BR',
                            'postalCode' => '01001-000',
                            'internationalPhone' => [
                                'countryCode' => '55',
                                'nationalNumber' => '1234567890',
                            ],
                        ],
                    ],
                    'receiver' => [
                        'firstName' => 'Bob',
                        'lastName' => 'Souza',
                        'accountReferenceNumber' => '2000012345',
                        'taxId' => '98765432100',
                        'address' => [
                            'extendedAddress' => '2B',
                            'streetAddress' => 'Avenida Brasil, 200',
                            'locality' => 'Rio de Janeiro',
                            'region' => 'RJ',
                            'countryCodeAlpha2' => 'BR',
                            'postalCode' => '20040-002',
                            'internationalPhone' => [
                                'countryCode' => '55',
                                'nationalNumber' => '9876543210',
                            ],
                        ],
                    ],
                ],
            ];

            $transactionGateway->sale($transactionParams);
        }
    }
}
