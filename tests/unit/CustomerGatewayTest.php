<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class CustomerGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->customer();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\CustomerGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function customerResponse()
    {
        return ['customer' => ['id' => 'cust_123', 'firstName' => 'John', 'lastName' => 'Smith']];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Customer::create(['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\Customer::update('the_id', ['invalidKey' => 'foo']);
    }

    public function testFind_throwsIfNullId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected customer id to be set');
        Braintree\Customer::find(null);
    }

    public function testFind_throwsIfInvalidCharacterId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Customer::find('invalid id!');
    }

    public function testUpdate_throwsIfInvalidId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Customer::update('invalid id!', []);
    }

    public function testDelete_throwsIfInvalidId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Customer::delete('invalid id!');
    }

    public function testCredit_throwsIfInvalidId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Customer::credit('invalid id!', ['amount' => '10.00']);
    }

    public function testSale_throwsIfInvalidId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid customer id.');
        Braintree\Customer::sale('invalid id!', ['amount' => '10.00']);
    }

    public function testSearch_throwsIfTermHasNoOperator()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Operator must be provided');
        Braintree\Customer::search([Braintree\CustomerSearch::company()]);
    }

    public function testCreateSignature_doesNotIncludeCustomerIdOnCreditCard()
    {
        $signature = Braintree\CustomerGateway::createSignature();
        $creditCardSignatures = array_filter($signature, function ($el) {
            // phpcs:ignore
            return is_array($el) && array_key_exists('creditCard', $el);
        });
        $creditCardSignature = array_shift($creditCardSignatures)['creditCard'];
        $this->assertNotContains('customerId', $creditCardSignature);
    }

    public function testUpdateSignature_addsUpdateExistingTokenOptionToCreditCard()
    {
        $signature = Braintree\CustomerGateway::updateSignature();
        $creditCardSignature = null;
        foreach ($signature as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('creditCard', $value)) {
                $creditCardSignature = $value['creditCard'];
            }
        }
        $this->assertNotNull($creditCardSignature);
        foreach ($creditCardSignature as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('options', $value)) {
                $this->assertContains('updateExistingToken', $value['options']);
            }
        }
    }

    public function testCreateSignature_includesInternationalPhone()
    {
        $signature = Braintree\CustomerGateway::createSignature();
        $this->assertContains(['internationalPhone' => ['countryCode', 'nationalNumber']], $signature);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->customerResponse());
        $result = $gateway->create(['firstName' => 'John', 'lastName' => 'Smith']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\Customer', $result->customer);
        $this->assertEquals('cust_123', $result->customer->id);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create(['firstName' => 'John']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected customer or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->create(['firstName' => 'John']);
    }

    public function testDelete_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->delete('valid-id');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testFind_returnsCustomer()
    {
        $gateway = $this->gatewayWithMock('get', $this->customerResponse());
        $result = $gateway->find('cust_123');
        $this->assertInstanceOf('Braintree\Customer', $result);
        $this->assertEquals('cust_123', $result->id);
    }

    public function testFind_throwsNotFoundWhenCustomerMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('customer with id missing-id not found');
        $gateway = Helper::integrationMerchantGateway()->customer();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\CustomerGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        $gateway->find('missing-id');
    }
}
