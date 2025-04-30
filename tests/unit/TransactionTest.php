<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class TransactionTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $t = Braintree\Transaction::factory([
            'creditCard' => ['expirationMonth' => '05', 'expirationYear' => '2010', 'bin' => '510510', 'last4' => '5100'],
            'customer' => [],
            'billing' => [],
            'descriptor' => [],
            'shipping' => [],
            'subscription' => ['billingPeriodStartDate' => '1983-07-12'],
            'statusHistory' => []
        ]);
        $this->expectError();
        $t->foo;
    }

    public function testCloneTransaction_RaisesErrorOnInvalidProperty()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Transaction::cloneTransaction('an id', ['amount' => '123.45', 'invalidProperty' => 'foo']);
    }

    public function testErrorsWhenFindWithBlankString()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\Transaction::find('');
    }

    public function testInitializationWithoutArguments()
    {
        $transaction = Braintree\Transaction::factory([]);
        $this->assertTrue($transaction instanceof Braintree\Transaction);
    }

    public function testSaleWithSkipAdvancedFraudCheckingValueAsTrue()
    {
        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->once())
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                $this->assertTrue($params["transaction"]["options"]["skipAdvancedFraudChecking"]);
            }));
        $transactionGateway->sale([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationDate' => '05/2009',
            ],
            'options' => [
                'skipAdvancedFraudChecking' => true
            ]
        ]);
    }

    public function testSaleWithSkipAdvancedFraudCheckingValueAsFalse()
    {
        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->once())
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                $this->assertFalse($params["transaction"]["options"]["skipAdvancedFraudChecking"]);
            }));
        $transactionGateway->sale([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationDate' => '05/2009',
            ],
            'options' => [
                'skipAdvancedFraudChecking' => false
            ]
        ]);
    }

    public function testSaleWithoutSkipAdvancedFraudCheckingOption()
    {
        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->once())
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                $this->assertArrayNotHasKey("skipAdvancedFraudChecking", $params["transaction"]["options"]);
            }));
        $transactionGateway->sale([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationDate' => '05/2009',
            ],
            'options' => [
                'submitForSettlement' => true
            ]
        ]);
    }

    public function testSaleWithExternalNetworkTokenOption()
    {
        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->once())
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                $this->assertEquals("/wAAAAAAAcb8AlGUF/1JQEkAAAA=", $params["transaction"]["creditCard"]["networkTokenizationAttributes"]["cryptogram"]);
                $this->assertEquals("45310020105", $params["transaction"]["creditCard"]["networkTokenizationAttributes"]["ecommerceIndicator"]);
                $this->assertEquals("05", $params["transaction"]["creditCard"]["networkTokenizationAttributes"]["tokenRequestorId"]);
            }));
        $transactionGateway->sale([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'networkTokenizationAttributes' => [
                    'cryptogram' => '/wAAAAAAAcb8AlGUF/1JQEkAAAA=',
                    'ecommerceIndicator' => '45310020105',
                    'tokenRequestorId' => '05'
                ]
            ]
        ]);
    }

    public function testSaleAcceptsPaymentFacilitatorOptions()
    {
        $transactionGateway = $this->mockTransactionGatewayDoCreate();
        $transactionGateway
            ->expects($this->once())
            ->method('_doCreate')
            ->will($this->returnCallback(function ($path, $params) {
                isset($params["transaction"]["paymentFacilitator"]);
            }));

        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',
            'paymentFacilitator' => [
                'paymentFacilitatorId' => '98765432109',
                'subMerchant' => [
                    'referenceNumber' => '123456789012345',
                    'taxId' => '99112233445577',
                    'legalName' => 'a-sub-merchant',
                    'address' => [
                        'streetAddress' => '10880 Ibitinga',
                        'locality' => 'Araraquara',
                        'region' => 'SP',
                        'countryCodeAlpha2' => 'BR',
                        'postalCode' => '13525000',
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

    public function testTransactionWithMetaCheckoutCardAttributes()
    {
        $transaction = Braintree\Transaction::factory([
            'amount' => '420',
            'metaCheckoutCard' => [
                'expirationMonth' => "12",
                'expirationYear' => "2024",
                'bin' => "401288",
                'last4' => "1881",
            ]
        ]);

        $this->assertTrue($transaction->metaCheckoutCardDetails instanceof Braintree\Transaction\MetaCheckoutCardDetails);
    }

    public function testTransactionWithMetaCheckoutTokenAttributes()
    {
        $transaction = Braintree\Transaction::factory([
            'amount' => '420',
            'metaCheckoutToken' => [
                'expirationMonth' => "12",
                'expirationYear' => "2024",
                'bin' => "401288",
                'last4' => "1881",
            ]
        ]);

        $this->assertTrue($transaction->metaCheckoutTokenDetails instanceof Braintree\Transaction\MetaCheckoutTokenDetails);
    }

    public function testTransactionWithSepaDebitAccountDetail()
    {
        $transaction = Braintree\Transaction::factory([
            'id' => '123',
            'type' => 'sale',
            'amount' => '12.34',
            'status' => 'settled',
            'customer' => [],
            'creditCard' => ['expirationMonth' => '05', 'expirationYear' => '2010', 'bin' => '510510', 'last4' => '5100'],
            'createdAt' => DateTime::createFromFormat('Ymd', '20121212'),
            'sepaDebitAccountDetail' => [
                [
                    'last4' => "1234",
                ],
            ]
        ]);

        $details = $transaction -> sepaDirectDebitAccountDetails -> toArray()[0];
        $this->assertEquals("1234", $details["last4"]);
    }

    private function mockTransactionGatewayDoCreate()
    {
        return $this->getMockBuilder('Braintree\TransactionGateway')
            ->setConstructorArgs(array(Braintree\Configuration::gateway()))
            ->setMethods(array('_doCreate'))
            ->getMock();
    }
}
