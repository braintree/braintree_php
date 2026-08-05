<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class LocalPaymentTest extends Setup
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function createGateway()
    {
        return new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'pwpp_multi_account_merchant',
            'publicKey' => 'pwpp_multi_account_merchant_public_key',
            'privateKey' => 'pwpp_multi_account_merchant_private_key'
        ]);
    }

    public function testCreateMbway()
    {
        $gateway = $this->createGateway();
        $result = $gateway->localPaymentContext()->create([
            'amount' => [
                'value' => '10.00',
                'currencyCode' => 'EUR'
            ],
            'type' => Braintree\LocalPaymentType::MBWAY,
            'payerInfo' => [
                'givenName' => 'John',
                'surname' => 'Doe',
                'phoneNumber' => '912345678',
                'phoneCountryCode' => '351'
            ],
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
            'merchantAccountId' => 'eur_pwpp_multi_account_merchant_account'
        ]);

        $this->assertEquals(true, $result->success);
        $localPayment = $result->localPayment;
        $this->assertNotNull($localPayment->id);
        $this->assertNotNull($localPayment->legacyId);
        $this->assertEquals('MBWAY', $localPayment->type);
        $this->assertEquals('eur_pwpp_multi_account_merchant_account', $localPayment->merchantAccountId);
        $this->assertNotNull($localPayment->amount);
        $this->assertEquals('10.00', $localPayment->amount->value);
        $this->assertEquals('EUR', $localPayment->amount->currencyCode);
    }

    public function testCreateCrypto()
    {
        $gateway = $this->createGateway();
        $result = $gateway->localPaymentContext()->create([
            'amount' => [
                'value' => '25.00',
                'currencyCode' => 'USD'
            ],
            'type' => Braintree\LocalPaymentType::CRYPTO,
            'payerInfo' => [
                'givenName' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com'
            ],
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
            'merchantAccountId' => 'usd_pwpp_multi_account_merchant_account'
        ]);

        $this->assertEquals(true, $result->success);
        $localPayment = $result->localPayment;
        $this->assertNotNull($localPayment->id);
        $this->assertNotNull($localPayment->legacyId);
        $this->assertEquals('CRYPTO', $localPayment->type);
        $this->assertEquals('usd_pwpp_multi_account_merchant_account', $localPayment->merchantAccountId);
        $this->assertNotNull($localPayment->amount);
        $this->assertEquals('25.00', $localPayment->amount->value);
        $this->assertEquals('USD', $localPayment->amount->currencyCode);
    }

    public function testCreateWithShippingAddress()
    {
        $gateway = $this->createGateway();
        $result = $gateway->localPaymentContext()->create([
            'amount' => [
                'value' => '10.00',
                'currencyCode' => 'EUR'
            ],
            'type' => Braintree\LocalPaymentType::MBWAY,
            'payerInfo' => [
                'givenName' => 'John',
                'surname' => 'Doe',
                'phoneNumber' => '912345678',
                'phoneCountryCode' => '351',
                'billingAddress' => [
                    'countryCode' => 'PT',
                    'streetAddress' => 'Rua da Liberdade, 79',
                    'locality' => 'Lisbon',
                    'postalCode' => '1250-140'
                ],
                'shippingAddress' => [
                    'countryCode' => 'PT',
                    'streetAddress' => 'Av. da República, 123',
                    'locality' => 'Porto',
                    'postalCode' => '4000-001'
                ]
            ],
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
            'merchantAccountId' => 'eur_pwpp_multi_account_merchant_account'
        ]);

        $this->assertEquals(true, $result->success);
        $localPayment = $result->localPayment;
        $this->assertNotNull($localPayment->id);
        $this->assertNotNull($localPayment->legacyId);
        $this->assertEquals('MBWAY', $localPayment->type);
    }

    public function testCreateWithOnlyRequiredFields()
    {
        $gateway = $this->createGateway();
        $result = $gateway->localPaymentContext()->create([
            'amount' => [
                'value' => '15.00',
                'currencyCode' => 'EUR'
            ],
            'type' => Braintree\LocalPaymentType::MBWAY,
            'payerInfo' => [
                'givenName' => 'John',
                'surname' => 'Doe',
                'phoneNumber' => '912345678',
                'phoneCountryCode' => '351'
            ],
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
            'merchantAccountId' => 'eur_pwpp_multi_account_merchant_account'
        ]);

        $this->assertEquals(true, $result->success);
        $localPayment = $result->localPayment;
        $this->assertNotNull($localPayment->id);
        $this->assertNotNull($localPayment->legacyId);
        $this->assertEquals('MBWAY', $localPayment->type);
        $this->assertEquals('eur_pwpp_multi_account_merchant_account', $localPayment->merchantAccountId);
        $this->assertNotNull($localPayment->amount);
        $this->assertEquals('15.00', $localPayment->amount->value);
        $this->assertEquals('EUR', $localPayment->amount->currencyCode);
    }

    public function testFind()
    {
        $gateway = $this->createGateway();
        $createResult = $gateway->localPaymentContext()->create([
            'amount' => [
                'value' => '10.00',
                'currencyCode' => 'EUR'
            ],
            'type' => Braintree\LocalPaymentType::MBWAY,
            'payerInfo' => [
                'givenName' => 'John',
                'surname' => 'Doe',
                'phoneNumber' => '912345678',
                'phoneCountryCode' => '351'
            ],
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
            'merchantAccountId' => 'eur_pwpp_multi_account_merchant_account'
        ]);

        $this->assertEquals(true, $createResult->success);

        $localPayment = $gateway->localPaymentContext()->find($createResult->localPayment->id);
        $this->assertNotNull($localPayment);
        $this->assertEquals($createResult->localPayment->id, $localPayment->id);
        $this->assertEquals('MBWAY', $localPayment->type);
        $this->assertEquals('eur_pwpp_multi_account_merchant_account', $localPayment->merchantAccountId);
        $this->assertNotNull($localPayment->amount);
        $this->assertEquals('10.00', $localPayment->amount->value);
        $this->assertEquals('EUR', $localPayment->amount->currencyCode);
    }

    public function testFindNonexistent()
    {
        $gateway = $this->createGateway();
        $this->expectException('Braintree\Exception\NotFound');
        $gateway->localPaymentContext()->find('nonexistent_id');
    }

    public function testValidationErrors()
    {
        $gateway = $this->createGateway();
        $result = $gateway->localPaymentContext()->create([
            'amount' => [
                'value' => 'invalid',
                'currencyCode' => 'EUR'
            ],
            'type' => Braintree\LocalPaymentType::MBWAY,
            'payerInfo' => [
                'givenName' => 'John',
                'surname' => 'Doe',
                'phoneNumber' => '912345678',
                'phoneCountryCode' => '351'
            ],
            'returnUrl' => 'https://example.com/return',
            'cancelUrl' => 'https://example.com/cancel',
            'merchantAccountId' => 'eur_pwpp_multi_account_merchant_account'
        ]);

        $this->assertEquals(false, $result->success);
        $this->assertNotNull($result->errors);
    }
}
