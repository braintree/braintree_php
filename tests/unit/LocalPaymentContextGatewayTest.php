<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class LocalPaymentContextGatewayTest extends Setup
{
    private function gatewayReturning($response)
    {
        $mockGraphQLClient = $this->createMock('\Braintree\GraphQLClient');
        $mockGraphQLClient->method('query')->willReturn($response);
        return new Braintree\LocalPaymentContextGateway($mockGraphQLClient);
    }

    public function testCreateSignature()
    {
        $expected = [
            ['amount' => ['value', 'currencyCode']],
            'type',
            ['payerInfo' => [
                'givenName',
                'surname',
                'email',
                'phoneNumber',
                'phoneCountryCode',
                ['billingAddress' => [
                    'countryCode',
                    'streetAddress',
                    'extendedAddress',
                    'locality',
                    'region',
                    'postalCode'
                ]],
                ['shippingAddress' => [
                    'countryCode',
                    'streetAddress',
                    'extendedAddress',
                    'locality',
                    'region',
                    'postalCode'
                ]]
            ]],
            'returnUrl',
            'cancelUrl',
            'merchantAccountId',
            'orderId',
            'countryCode',
            'expiryDate',
            'paymentId'
        ];

        $this->assertEquals($expected, Braintree\LocalPaymentContextGateway::createSignature());
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        $this->gatewayReturning([])->create(['invalidKey' => 'foo']);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $response = [
            'data' => [
                'createLocalPaymentContext' => [
                    'paymentContext' => [
                        'id' => 'context_id',
                        'legacyId' => 'legacy_id',
                        'type' => 'MBWAY',
                        'paymentId' => 'payment_id',
                        'approvalUrl' => 'https://example.com/approve',
                        'merchantAccountId' => 'eur_account',
                    ]
                ]
            ]
        ];

        $result = $this->gatewayReturning($response)->create([
            'amount' => ['value' => '10.00', 'currencyCode' => 'EUR'],
            'type' => 'MBWAY',
            'merchantAccountId' => 'eur_account',
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
        ]);

        $this->assertTrue($result->success);
        $this->assertInstanceOf('Braintree\LocalPayment', $result->localPayment);
        $this->assertEquals('context_id', $result->localPayment->id);
        $this->assertEquals('https://example.com/approve', $result->localPayment->approvalUrl);
    }

    public function testCreate_returnsErrorResultOnValidationErrors()
    {
        $response = [
            'errors' => [
                ['message' => 'Something went wrong'],
            ]
        ];

        $result = $this->gatewayReturning($response)->create([
            'amount' => ['value' => '10.00', 'currencyCode' => 'EUR'],
            'type' => 'MBWAY',
        ]);

        $this->assertFalse($result->success);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testCreate_throwsServerErrorWhenResponseCannotBeParsed()
    {
        $this->expectException('Braintree\Exception\ServerError');
        $this->expectExceptionMessage("Couldn't parse server response");
        $response = [
            'data' => [
                'createLocalPaymentContext' => []
            ]
        ];

        $this->gatewayReturning($response)->create([
            'amount' => ['value' => '10.00', 'currencyCode' => 'EUR'],
            'type' => 'MBWAY',
        ]);
    }

    public function testCreate_forwardsPreparedAttributesAsVariables()
    {
        $response = [
            'data' => [
                'createLocalPaymentContext' => [
                    'paymentContext' => ['id' => 'context_id']
                ]
            ]
        ];

        $attributes = [
            'amount' => ['value' => '10.00', 'currencyCode' => 'EUR'],
            'type' => 'MBWAY',
            'merchantAccountId' => 'eur_account',
            'orderId' => 'order_1',
            'countryCode' => 'PT',
            'expiryDate' => '2026-12-31',
            'paymentId' => 'payment_1',
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
        ];

        $mockGraphQLClient = $this->createMock('\Braintree\GraphQLClient');
        $mockGraphQLClient->expects($this->once())
            ->method('query')
            ->with(
                Braintree\LocalPaymentContextGateway::CREATE_LOCAL_PAYMENT_CONTEXT_MUTATION,
                ['input' => ['paymentContext' => $attributes]]
            )
            ->willReturn($response);

        $gateway = new Braintree\LocalPaymentContextGateway($mockGraphQLClient);
        $gateway->create($attributes);
    }

    public function testFind_returnsLocalPayment()
    {
        $response = [
            'data' => [
                'node' => [
                    'id' => 'context_id',
                    'legacyId' => 'legacy_id',
                    'type' => 'MBWAY',
                    'merchantAccountId' => 'eur_account',
                ]
            ]
        ];

        $localPayment = $this->gatewayReturning($response)->find('context_id');

        $this->assertInstanceOf('Braintree\LocalPayment', $localPayment);
        $this->assertEquals('context_id', $localPayment->id);
        $this->assertEquals('eur_account', $localPayment->merchantAccountId);
    }

    public function testFind_throwsNotFoundOnValidationErrors()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('Local payment context not found');
        $response = [
            'errors' => [
                ['message' => 'Not found'],
            ]
        ];

        $this->gatewayReturning($response)->find('missing_id');
    }

    public function testFind_throwsNotFoundWhenNodeMissing()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('Local payment context not found');
        $response = [
            'data' => []
        ];

        $this->gatewayReturning($response)->find('missing_id');
    }
}
