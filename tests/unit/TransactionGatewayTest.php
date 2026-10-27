<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class TransactionGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->transaction();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\TransactionGateway', '_http');
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function transactionResponse()
    {
        return ['transaction' => [
            'id' => 'txn_123',
            'amount' => '10.00',
            'status' => 'authorized',
            'type' => 'sale',
            'currencyIsoCode' => 'USD',
            'statusHistory' => [],
            'addOns' => [],
            'discounts' => [],
            'disputes' => [],
            'customFields' => '',
        ]];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->transaction();
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Transaction::sale(['amount' => '10.00', 'invalidKey' => 'foo']);
    }

    public function testFind_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected transaction id to be set');
        Braintree\Transaction::find('');
    }

    public function testVoid_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected transaction id to be set');
        Braintree\Transaction::void('');
    }

    public function testSubmitForSettlement_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected transaction id to be set');
        Braintree\Transaction::submitForSettlement('');
    }

    public function testRefund_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected transaction id to be set');
        Braintree\Transaction::refund('');
    }

    public function testFind_throwsIfTraversalId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid transaction id.');
        Braintree\Transaction::find('../customers/cust_123');
    }

    public function testVoid_throwsIfTraversalId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid transaction id.');
        Braintree\Transaction::void('../transactions/other_txn_id');
    }

    public function testSubmitForSettlement_throwsIfTraversalId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid transaction id.');
        Braintree\Transaction::submitForSettlement('../transactions/other_txn_id');
    }

    public function testRefund_throwsIfTraversalId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid transaction id.');
        Braintree\Transaction::refund('../customers/cust_123');
    }

    public function testCloneTransaction_throwsIfTraversalId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid transaction id.');
        Braintree\Transaction::cloneTransaction('../customers/cust_123', ['amount' => '10.00']);
    }

    public function testCloneSignature()
    {
        $expected = ['amount', 'channel', ['options' => ['submitForSettlement']]];
        $this->assertEquals($expected, Braintree\TransactionGateway::cloneSignature());
    }

    public function testCreateSignature_containsExpectedKeys()
    {
        $sig = Braintree\TransactionGateway::createSignature();
        $this->assertContains('amount', $sig);
        $this->assertContains('customerId', $sig);
        $this->assertContains('paymentMethodToken', $sig);
        $this->assertContains('paymentMethodNonce', $sig);
        $this->assertContains('merchantAccountId', $sig);
        $this->assertContains('orderId', $sig);
        $this->assertContains('channel', $sig);
        $this->assertContains('shippingAddressId', $sig);
    }

    public function testSaleSignature_containsSubmitForSettlement()
    {
        $sig = Braintree\TransactionGateway::createSignature();
        $optionsSig = null;
        foreach ($sig as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('options', $value)) {
                $optionsSig = $value['options'];
                break;
            }
        }
        $this->assertContains('submitForSettlement', $optionsSig);
    }

    public function testCreateSignature_includesExternalVaultFields()
    {
        $sig = Braintree\TransactionGateway::createSignature();
        $externalVault = null;
        foreach ($sig as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('externalVault', $value)) {
                $externalVault = $value['externalVault'];
                break;
            }
        }
        $this->assertNotNull($externalVault);
        $this->assertContains('previousNetworkTransactionId', $externalVault);
        $this->assertContains('previousMastercardTransactionLinkId', $externalVault);
    }

    public function testSale_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->transactionResponse());
        $result = $gateway->sale([
            'amount' => '10.00',
            'paymentMethodNonce' => 'fake-valid-nonce',
        ]);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\Transaction', $result->transaction);
        $this->assertEquals('txn_123', $result->transaction->id);
    }

    public function testSale_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->sale(['amount' => '10.00', 'paymentMethodNonce' => 'fake-nonce']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testSale_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected transaction or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->sale(['amount' => '10.00', 'paymentMethodNonce' => 'fake-nonce']);
    }

    public function testCredit_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->transactionResponse());
        $result = $gateway->credit([
            'amount' => '10.00',
            'paymentMethodNonce' => 'fake-valid-nonce',
        ]);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testFind_returnsTransaction()
    {
        $gateway = $this->gatewayWithMock('get', $this->transactionResponse());
        $result = $gateway->find('txn_123');
        $this->assertInstanceOf('Braintree\Transaction', $result);
        $this->assertEquals('txn_123', $result->id);
    }

    public function testFind_throwsNotFoundWhenMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('transaction with id missing-id not found');
        $gateway = Helper::integrationMerchantGateway()->transaction();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\TransactionGateway', '_http');
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        $prop->setValue($gateway, $mock);
        $gateway->find('missing-id');
    }

    public function testVoid_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->transactionResponse());
        $result = $gateway->void('txn_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testVoid_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->void('txn_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testReverse_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->transactionResponse());
        $result = $gateway->reverse('txn_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testReverse_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->reverse('txn_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testReverseNoValidate_returnsTransaction()
    {
        $gateway = $this->gatewayWithMock('post', $this->transactionResponse());
        $result = $gateway->reverseNoValidate('txn_123');
        $this->assertInstanceOf('Braintree\Transaction', $result);
    }

    public function testReverseNoValidate_throwsValidationException()
    {
        $this->expectException('Braintree\Exception\ValidationsFailed');
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $gateway->reverseNoValidate('txn_123');
    }

    public function testReverseSignature_containsApiRequestKey()
    {
        $sig = Braintree\TransactionGateway::reverseSignature();
        $this->assertContains('apiRequestKey', $sig);
    }

    public function testReverse_throwsForInvalidId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('../transactions/victim is an invalid transaction id.');
        Helper::integrationMerchantGateway()->transaction()->reverse('../transactions/victim');
    }

    public function testSubmitForSettlement_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->transactionResponse());
        $result = $gateway->submitForSettlement('txn_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testSubmitForSettlement_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->submitForSettlement('txn_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testRefund_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->transactionResponse());
        $result = $gateway->refund('txn_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRefund_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->refund('txn_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testRefundSignature_containsSurchargeAmount()
    {
        $sig = Braintree\TransactionGateway::refundSignature();
        $this->assertContains('surchargeAmount', $sig);
    }

    public function testAdjustAuthorization_throwsIfEmptyId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected transaction id to be set');
        Helper::integrationMerchantGateway()->transaction()->adjustAuthorization('', '10.00');
    }

    public function testAdjustAuthorization_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->transactionResponse());
        $result = $gateway->adjustAuthorization('txn_123', '15.00');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testSaleNoValidate_returnsTransaction()
    {
        $gateway = $this->gatewayWithMock('post', $this->transactionResponse());
        $result = $gateway->saleNoValidate(['amount' => '10.00', 'paymentMethodNonce' => 'fake-nonce']);
        $this->assertInstanceOf('Braintree\Transaction', $result);
    }

    public function testSaleNoValidate_throwsValidationException()
    {
        $this->expectException('Braintree\Exception\ValidationsFailed');
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $gateway->saleNoValidate(['amount' => '10.00', 'paymentMethodNonce' => 'fake-nonce']);
    }

    public function testSearch_throwsOnTimeout()
    {
        $this->expectException('Braintree\Exception\RequestTimeout');
        $gateway = $this->gatewayWithMock('post', ['noSearchResults' => true]);
        $gateway->search([Braintree\TransactionSearch::id()->is('txn_123')]);
    }

    public function testSubmitForSettlement_withShippingAddressIdAndDetails_returnsSuccessfulResult()
    {
        $gateway = Helper::integrationMerchantGateway()->transaction();
        $mock = $this->createMock('\Braintree\Http');

        $mock->expects($this->once())
        ->method('put')
        ->willReturnCallback(function ($path, $params) {
            $this->assertEquals('address_123', $params['transaction']['shippingAddressId']);
            $this->assertEquals([
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'streetAddress' => '456 Oak Ave',
                'locality' => 'New York',
                'region' => 'NY',
                'postalCode' => '10001',
                'countryCodeAlpha2' => 'US',
            ], $params['transaction']['shipping']);

            return $this->transactionResponse();
        });

        $prop = new \ReflectionProperty('Braintree\TransactionGateway', '_http');
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        $prop->setValue($gateway, $mock);

        $result = $gateway->submitForSettlement('txn_123', null, [
        'shippingAddressId' => 'address_123',
        'shipping' => [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'streetAddress' => '456 Oak Ave',
            'locality' => 'New York',
            'region' => 'NY',
            'postalCode' => '10001',
            'countryCodeAlpha2' => 'US',
        ],
        ]);

        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }
}
