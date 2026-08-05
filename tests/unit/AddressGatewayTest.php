<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class AddressGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->address();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\AddressGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function addressResponse()
    {
        return ['address' => [
            'id' => 'addr_123',
            'customerId' => 'cust_123',
            'streetAddress' => '123 Main St',
            'locality' => 'Chicago',
            'region' => 'IL',
            'postalCode' => '60601',
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
        $gateway->address();
    }

    public function testCreateSignature_containsExpectedKeys()
    {
        $sig = Braintree\AddressGateway::createSignature();
        $this->assertContains('firstName', $sig);
        $this->assertContains('lastName', $sig);
        $this->assertContains('streetAddress', $sig);
        $this->assertContains('customerId', $sig);
        $this->assertContains('postalCode', $sig);
    }

    public function testUpdateSignature_matchesCreateSignature()
    {
        $this->assertEquals(
            Braintree\AddressGateway::createSignature(),
            Braintree\AddressGateway::updateSignature()
        );
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Address::create(['customerId' => 'cust_123', 'invalidKey' => 'foo']);
    }

    public function testCreate_throwsIfMissingCustomerId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected customer id to be set');
        Braintree\Address::create(['streetAddress' => '123 Main St']);
    }

    public function testCreate_throwsIfInvalidCustomerId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Address::create(['customerId' => 'invalid id!', 'streetAddress' => '123 Main St']);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->addressResponse());
        $result = $gateway->create(['customerId' => 'cust_123', 'streetAddress' => '123 Main St']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\Address', $result->address);
        $this->assertEquals('addr_123', $result->address->id);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create(['customerId' => 'cust_123', 'streetAddress' => '123 Main St']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected address or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->create(['customerId' => 'cust_123', 'streetAddress' => '123 Main St']);
    }

    public function testFind_throwsIfEmptyAddressId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected address id to be set');
        Braintree\Address::find('cust_123', '');
    }

    public function testFind_throwsIfInvalidAddressId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid address id.');
        Braintree\Address::find('cust_123', 'invalid!');
    }

    public function testFind_throwsIfTraversalCustomerId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Address::find('../../foo', 'addr_123');
    }

    public function testFind_throwsIfTraversalAddressId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid address id.');
        Braintree\Address::find('cust_123', '../../foo');
    }

    public function testFind_returnsAddress()
    {
        $gateway = $this->gatewayWithMock('get', $this->addressResponse());
        $result = $gateway->find('cust_123', 'addr_123');
        $this->assertInstanceOf('Braintree\Address', $result);
        $this->assertEquals('addr_123', $result->id);
    }

    public function testFind_throwsNotFoundWhenMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('address for customer cust_123 with id addr_123 not found.');
        $gateway = Helper::integrationMerchantGateway()->address();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\AddressGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        $gateway->find('cust_123', 'addr_123');
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Address::update('cust_123', 'addr_123', ['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfEmptyAddressId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected address id to be set');
        Braintree\Address::update('cust_123', '', []);
    }

    public function testUpdate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->addressResponse());
        $result = $gateway->update('cust_123', 'addr_123', ['streetAddress' => '456 Oak Ave']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testUpdate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->update('cust_123', 'addr_123', ['streetAddress' => '456 Oak Ave']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testDelete_throwsIfEmptyAddressId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected address id to be set');
        Braintree\Address::delete('cust_123', '');
    }

    public function testDelete_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->delete('cust_123', 'addr_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }
}
