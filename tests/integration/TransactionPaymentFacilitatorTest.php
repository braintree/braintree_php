<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test;
use Test\Setup;
use Test\Braintree\CreditCardNumbers\CardTypeIndicators;
use Braintree;

class TransactionPaymentFacilitatorTest extends Setup
{
    public function testSaleWithSubMerchantAndPaymentFacilitator()
    {
        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',
            'transactionSource' => 'moto',
            'merchantAccountId' => 'card_processor_brl_payfac',
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '06/2026',
                'cvv' => '123',
            ],
            'descriptor' => [
                'name' => 'companynme12*product12',
                'phone' => '1232344444',
                'url' => 'example.com',
            ],
            'billing' => [
                'firstName' => 'Bob James',
                'countryCodeAlpha2' => 'CA',
                'extendedAddress' => '',
                'locality' => 'Trois-Rivires',
                'region' => 'QC',
                'postalCode' => 'G8Y 156',
                'streetAddress' => '2346 Boul Lane',
            ],
            'paymentFacilitator' => [
                'paymentFacilitatorId' => '98765432109',
                'subMerchant' => [
                    'referenceNumber' => '123456789012345',
                    'taxId' => '99112233445577',
                    'legalName' => 'Fooda',
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
            'options' => [
                'storeInVaultOnSuccess' => true,
            ],
        ];

        $result = Braintree\Transaction::sale($transactionParams);

        $this->assertTrue($result->success);
        $this->assertEquals(Braintree\Transaction::AUTHORIZED, $result->transaction->status);
    }

    public function testSaleWithSubMerchantAndPaymentFacilitatorForNonBrazilMerchant()
    {
        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '06/2026',
                'cvv' => '123',
            ],
            'descriptor' => [
                'name' => 'companynme12*product12',
                'phone' => '1232344444',
                'url' => 'example.com',
            ],
            'billing' => [
                'firstName' => 'Bob James',
                'countryCodeAlpha2' => 'CA',
                'extendedAddress' => '',
                'locality' => 'Trois-Rivires',
                'region' => 'QC',
                'postalCode' => 'G8Y 156',
                'streetAddress' => '2346 Boul Lane',
            ],
            'paymentFacilitator' => [
                'paymentFacilitatorId' => '98765432109',
                'subMerchant' => [
                    'referenceNumber' => '123456789012345',
                    'taxId' => '99112233445577',
                    'legalName' => 'Fooda',
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
            'options' => [
                'storeInVaultOnSuccess' => true,
            ],
        ];

        $ezp_gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'pp_credit_ezp_merchant',
            'publicKey' => 'pp_credit_ezp_merchant_public_key',
            'privateKey' => 'pp_credit_ezp_merchant_private_key'
        ]);
        $result = $ezp_gateway->transaction()->sale($transactionParams);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->errors;
        $this->assertEquals('97405', $errors[0]->code);
    }
}
