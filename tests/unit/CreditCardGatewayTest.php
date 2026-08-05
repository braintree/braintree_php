<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class CreditCardGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->creditCard();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\CreditCardGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function creditCardResponse()
    {
        return ['creditCard' => ['token' => 'tok_123', 'bin' => '411111', 'last4' => '1111']];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\CreditCard::create(['invalidKey' => 'foo']);
    }

    public function testUpdate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Braintree\CreditCard::update('the_token', ['invalidKey' => 'foo']);
    }

    public function testFind_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected credit card id to be set');
        Braintree\CreditCard::find('');
    }

    public function testFind_throwsIfInvalidCharacterToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid credit card token.');
        Braintree\CreditCard::find('invalid token!');
    }

    public function testFromNonce_throwsIfEmptyNonce()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected credit card id to be set');
        Braintree\CreditCard::fromNonce('');
    }

    public function testFromNonce_throwsIfInvalidNonce()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid credit card nonce.');
        Braintree\CreditCard::fromNonce('invalid nonce!');
    }

    public function testUpdate_throwsIfInvalidToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('is an invalid credit card token.');
        Braintree\CreditCard::update('invalid token!', []);
    }

    public function testDelete_throwsIfEmptyToken()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('expected credit card id to be set');
        Braintree\CreditCard::delete('');
    }

    public function testBillingAddressSignature()
    {
        $expected = [
            'firstName', 'lastName', 'company', 'countryCodeAlpha2', 'countryCodeAlpha3',
            'countryCodeNumeric', 'countryName', 'extendedAddress', 'locality', 'region',
            'postalCode', 'streetAddress', 'phoneNumber',
        ];
        $this->assertEquals($expected, Braintree\CreditCardGateway::billingAddressSignature());
    }

    public function testThreeDSecurePassThruSignature()
    {
        $expected = [
            'threeDSecurePassThru' => [
                'eciFlag', 'cavv', 'xid', 'threeDSecureVersion',
                'authenticationResponse', 'directoryResponse', 'cavvAlgorithm', 'dsTransactionId',
            ],
        ];
        $this->assertEquals($expected, Braintree\CreditCardGateway::threeDSecurePassThruSignature());
    }

    public function testCreateSignature_includesCustomerId()
    {
        $this->assertContains('customerId', Braintree\CreditCardGateway::createSignature());
    }

    public function testUpdateSignature_doesNotIncludeCustomerId()
    {
        $this->assertNotContains('customerId', Braintree\CreditCardGateway::updateSignature());
    }

    public function testUpdateSignature_addsUpdateExistingOptionToBillingAddress()
    {
        $signature = Braintree\CreditCardGateway::updateSignature();
        $billingAddress = null;
        foreach ($signature as $value) {
            // phpcs:ignore
            if (is_array($value) and array_key_exists('billingAddress', $value)) {
                $billingAddress = $value['billingAddress'];
            }
        }
        $this->assertNotNull($billingAddress);
        $this->assertContains(['options' => ['updateExisting']], $billingAddress);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->creditCardResponse());
        $result = $gateway->create([
            'customerId' => 'cust_123',
            'number' => '4111111111111111',
            'expirationDate' => '01/25',
        ]);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\CreditCard', $result->creditCard);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->create([
            'customerId' => 'cust_123',
            'number' => '4111111111111111',
            'expirationDate' => '01/25',
        ]);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }

    public function testCreate_throwsForUnexpectedResponse()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected address or apiErrorResponse');
        $gateway = $this->gatewayWithMock('post', ['unexpectedKey' => 'value']);
        $gateway->create([
            'customerId' => 'cust_123',
            'number' => '4111111111111111',
            'expirationDate' => '01/25',
        ]);
    }

    public function testUpdate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->creditCardResponse());
        $result = $gateway->update('valid-token', ['expirationDate' => '12/26']);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testUpdate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->update('valid-token', ['expirationDate' => '12/26']);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testDelete_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->delete('valid-token');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testFind_returnsCreditCard()
    {
        $gateway = $this->gatewayWithMock('get', $this->creditCardResponse());
        $result = $gateway->find('valid-token');
        $this->assertInstanceOf('Braintree\CreditCard', $result);
    }

    public function testFind_throwsNotFoundWhenTokenMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('credit card with token missing-token not found');
        $gateway = Helper::integrationMerchantGateway()->creditCard();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method('get')->will($this->throwException(new Braintree\Exception\NotFound()));
        $prop = new \ReflectionProperty('Braintree\CreditCardGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        $gateway->find('missing-token');
    }

    public function testFetchExpired_returnsArrayOfCreditCards()
    {
        $gateway = $this->gatewayWithMock('post', [
            'paymentMethods' => ['creditCard' => [['token' => 'tok_1', 'bin' => '411111', 'last4' => '1111']]],
        ]);
        $result = $gateway->fetchExpired(['tok_1']);
        $this->assertIsArray($result);
        $this->assertInstanceOf('Braintree\CreditCard', $result[0]);
    }

    public function testFetchExpiring_returnsArrayOfCreditCards()
    {
        $gateway = $this->gatewayWithMock('post', [
            'paymentMethods' => ['creditCard' => [['token' => 'tok_1', 'bin' => '411111', 'last4' => '1111']]],
        ]);
        $result = $gateway->fetchExpiring(mktime(0, 0, 0, 1, 1, 2025), mktime(0, 0, 0, 12, 31, 2025), ['tok_1']);
        $this->assertIsArray($result);
    }

    public function testExpired_returnsResourceCollection()
    {
        $gateway = $this->gatewayWithMock('post', [
            'searchResults' => ['pageSize' => 50, 'ids' => ['tok_1', 'tok_2']],
        ]);
        $result = $gateway->expired();
        $this->assertInstanceOf('Braintree\ResourceCollection', $result);
    }

    public function testExpiringBetween_returnsResourceCollection()
    {
        $gateway = $this->gatewayWithMock('post', [
            'searchResults' => ['pageSize' => 50, 'ids' => ['tok_1']],
        ]);
        $result = $gateway->expiringBetween(
            mktime(0, 0, 0, 1, 1, 2025),
            mktime(0, 0, 0, 12, 31, 2025)
        );
        $this->assertInstanceOf('Braintree\ResourceCollection', $result);
    }

    public function testCreateNoValidate_returnsCreditCard()
    {
        $gateway = $this->gatewayWithMock('post', $this->creditCardResponse());
        $result = $gateway->createNoValidate([
            'customerId' => 'cust_123',
            'number' => '4111111111111111',
            'expirationDate' => '01/25',
        ]);
        $this->assertInstanceOf('Braintree\CreditCard', $result);
    }

    public function testCreateNoValidate_throwsValidationException()
    {
        $this->expectException('Braintree\Exception\ValidationsFailed');
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $gateway->createNoValidate([
            'customerId' => 'cust_123',
            'number' => '4111111111111111',
            'expirationDate' => '01/25',
        ]);
    }
}
