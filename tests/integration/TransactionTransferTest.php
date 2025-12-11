<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Braintree;
use Test\Setup;

class TransactionTransferTest extends Setup
{
    public const TRANSFER_TYPE = ["account_to_account", "person_to_person", "wallet_transfer", "boleto_ticket"];

    public function testSaleWithValidAftTransferType()
    {
        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',
            'merchantAccountId' => 'aft_first_data_wallet_transfer',
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '06/2026',
                'cvv' => '123',
            ],
            'transfer' => [
                'type' => 'wallet_transfer',
                'sender' => [
                    'firstName' => 'Alice',
                    'middleName' => 'A',
                    'lastName' => 'Silva',
                    'accountReferenceNumber' => '1000012345',
                    'address' => [
                        'streetAddress' => '1st Main Road',
                        'locality' => 'Los Angeles',
                        'region' => 'CA',
                        'countryCodeAlpha2' => 'US',
                    ],
                    'dateOfBirth' => DateTime::createFromFormat('Y-m-d', '2012-04-10'),
                ],
                'receiver' => [
                    'firstName' => 'Bob',
                    'middleName' => 'A',
                    'lastName' => 'Souza',
                    'address' => [
                        'streetAddress' => '2nd Main Road',
                        'locality' => 'Los Angeles',
                        'region' => 'CA',
                        'countryCodeAlpha2' => 'US',
                    ],
                ],
            ],
        ];

        $result = Braintree\Transaction::sale($transactionParams);

        $this->assertTrue($result->success);
        $this->assertTrue($result->transaction->accountFundingTransaction);
        $this->assertEquals(Braintree\Transaction::AUTHORIZED, $result->transaction->status);
    }


    public function testSaleWithValidSdwoTransferType()
    {
        $transactionParams = [
           'type' => 'sale',
           'amount' => '100.00',
           'merchantAccountId' => 'card_processor_brl_sdwo',
           'creditCard' => [
               'number' => '4111111111111111',
               'expirationDate' => '06/2026',
               'cvv' => '123',
           ],
           'transfer' => [
               'type' => 'wallet_transfer',
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
                           'postalCode' => '01001-000',
                           'countryCodeAlpha2' => 'BR',
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
                           'postalCode' => '20040-002',
                           'countryCodeAlpha2' => 'BR',
                           'internationalPhone' => [
                               'countryCode' => '55',
                               'nationalNumber' => '9876543210',
                           ],
                       ],
                   ],
           ],
        ];

        $result = Braintree\Transaction::sale($transactionParams);

        $this->assertTrue($result->success);
        $this->assertEquals(Braintree\Transaction::AUTHORIZED, $result->transaction->status);
    }

    public function testSaleWithInvalidTransferType()
    {
        $transactionParams = [
            'type' => 'sale',
            'amount' => '100.00',

            'merchantAccountId' => 'aft_first_data_wallet_transfer',
            'creditCard' => [
                'number' => '4111111111111111',
                'expirationDate' => '06/2026',
                'cvv' => '123',
            ],
            'transfer' => [
                'type' => 'invalid_transfer',
            ],
        ];
        $result = Braintree\Transaction::sale($transactionParams);
        $errors = $result->errors->forKey('accountFundingTransaction')->errors;
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Error\Codes::TRANSFER_DETAILS_NOT_APPLICABLE, $errors[0]->code);
    }


    public function testSaleWithSubMerchantWithoutTransferBlock()
    {

        foreach (self::TRANSFER_TYPE as $type) {
            $type = $type;
            $transactionParams = [
                'type' => 'sale',
                'amount' => '100.00',

                'merchantAccountId' => 'card_processor_brl_sdwo',
                'creditCard' => [
                    'number' => '4111111111111111',
                    'expirationDate' => '06/2026',
                    'cvv' => '123',
                ],
                'descriptor' => [
                    'name' => 'companynme12*product1',
                    'phone' => '1232344444',
                    'url' => 'example.com',
                ],
                'billing' => [
                    'firstName' => 'Bob James',
                    'countryCodeAlpha2' => 'CA',
                    'extendedAddress' => '',
                    'locality' => 'Trois-Rivieres',
                    'region' => 'QC',
                    'postalCode' => 'G8Y 156',
                    'streetAddress' => '2346 Boul Lane',
                ],


                'options' => [
                    'storeInVaultOnSuccess' => true,
                ],
            ];

            $result = Braintree\Transaction::sale($transactionParams);
            $this->assertFalse($result->success);
            $errors = $result->errors->forKey('transaction')->errors;
            $this->assertEquals(Braintree\Error\Codes::TRANSFER_DETAILS_NOT_AVAILABLE, $errors[0]->code);
        }
    }



    public function testSaleWithNullTransferType()
    {
        $transactionParams = [
           'type' => 'sale',
           'amount' => '100.00',
           'merchantAccountId' => 'aft_first_data_wallet_transfer',
           'creditCard' => [
               'number' => '4111111111111111',
               'expirationDate' => '06/2026',
               'cvv' => '123',
           ],
           'transfer' => [
               'type' => null,
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
                           'postalCode' => '01001-000',
                           'countryCodeAlpha2' => 'BR',
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
                           'postalCode' => '20040-002',
                           'countryCodeAlpha2' => 'BR',
                           'internationalPhone' => [
                               'countryCode' => '55',
                               'nationalNumber' => '9876543210',
                           ],
                       ],
                   ],
           ],
        ];

        $result = Braintree\Transaction::sale($transactionParams);

        $this->assertTrue($result->success);
        $this->assertEquals(Braintree\Transaction::AUTHORIZED, $result->transaction->status);
    }
}
