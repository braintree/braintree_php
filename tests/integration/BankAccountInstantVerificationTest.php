<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test;
use Test\Setup;
use Braintree;

class BankAccountInstantVerificationTest extends Setup
{
    private const GATEWAY_CONFIG = [
        'environment' => 'development',
        'merchantId' => 'integration2_merchant_id',
        'publicKey' => 'integration2_public_key',
        'privateKey' => 'integration2_private_key'
    ];

    private const US_BANK_GATEWAY_CONFIG = [
        'environment' => 'development',
        'merchantId' => 'integration_merchant_id',
        'publicKey' => 'integration_public_key',
        'privateKey' => 'integration_private_key'
    ];

    private const ACH_MANDATE_TEXT = 'I authorize this transaction and future debits';

    public function setUp(): void
    {
        parent::setUp();
        Test\Helper::integration2MerchantConfig();
    }

    private function createGateway(): Braintree\Gateway
    {
        return new Braintree\Gateway(self::GATEWAY_CONFIG);
    }

    private function createUsBankGateway(): Braintree\Gateway
    {
        return new Braintree\Gateway(self::US_BANK_GATEWAY_CONFIG);
    }

    private function createMandateDateTime(): DateTime
    {
        $mandateAcceptedAt = new DateTime();
        $mandateAcceptedAt->sub(new \DateInterval('PT5M'));
        return $mandateAcceptedAt;
    }


    private function generateUsBankAccountNonceWithoutAchMandate(): string
    {
        $config = new Braintree\Configuration(self::US_BANK_GATEWAY_CONFIG);

        $requestBody = [
            'account_details' => [
                'account_number' => '567891234',
                'account_type' => 'CHECKING',
                'classification' => 'PERSONAL',
                'tokenized_account' => true,
                'last_4' => '1234'
            ],
            'institution_details' => [
                'bank_id' => [
                    'bank_code' => '021000021',
                    'country_code' => 'US'
                ]
            ],
            'account_holders' => [
                [
                    'ownership' => 'PRIMARY',
                    'full_name' => [
                        'name' => 'Dan Schulman'
                    ],
                    'name' => [
                        'given_name' => 'Dan',
                        'surname' => 'Schulman'
                    ]
                ]
            ]
        ];

        $fullUrl = $config->atmosphereBaseUrl() . '/v1/open-finance/tokenize-bank-account-details';
        $httpClient = new Braintree\Http($config);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Braintree-Version: 2019-01-01',
            'X-ApiVersion: 1'
        ];
        $response = $httpClient->_doUrlRequest('POST', $fullUrl, json_encode($requestBody), null, $headers);

        $responseData = json_decode($response['body'], true);
        if (!isset($responseData['tenant_token'])) {
            throw new \Exception('Open Banking tokenization failed: ' . print_r($response, true));
        }

        return $responseData['tenant_token'];
    }

    public function testCreateJwtWithValidRequest()
    {
        $request = new Braintree\BankAccountInstantVerificationJwtRequest();
        $request->businessName('15Ladders')
               ->returnUrl('https://example.com/success')
               ->cancelUrl('https://example.com/cancel');

        $gateway = $this->createGateway();
        $result = $gateway->bankAccountInstantVerification()->createJwt($request);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->bankAccountInstantVerificationJwt);
        $this->assertNotEmpty($result->bankAccountInstantVerificationJwt->getJwt());
    }

    public function testCreateJwtWithInvalidBusinessName()
    {
        $request = new Braintree\BankAccountInstantVerificationJwtRequest();
        $request->businessName('')
               ->returnUrl('https://example.com/return');

        $gateway = $this->createGateway();
        $result = $gateway->bankAccountInstantVerification()->createJwt($request);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors);
    }

    public function testCreateJwtWithInvalidUrls()
    {
        $request = new Braintree\BankAccountInstantVerificationJwtRequest();
        $request->businessName('15Ladders')
               ->returnUrl('not-a-valid-url')
               ->cancelUrl('also-not-valid');

        $gateway = $this->createUsBankGateway();
        $result = $gateway->bankAccountInstantVerification()->createJwt($request);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors);
    }

    public function testChargeUsBankWithAchMandate()
    {
        $gateway = $this->createUsBankGateway();
        $nonce = $this->generateUsBankAccountNonceWithoutAchMandate();
        $mandateAcceptedAt = $this->createMandateDateTime();

        $transactionResult = $gateway->transaction()->sale([
            'amount' => '12.34',
            'paymentMethodNonce' => $nonce,
            'merchantAccountId' => Test\Helper::usBankMerchantAccount(),
            'usBankAccount' => [
                'achMandateText' => self::ACH_MANDATE_TEXT,
                'achMandateAcceptedAt' => $mandateAcceptedAt->format('c')
            ],
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        $this->assertTrue($transactionResult->success);

        $transaction = $transactionResult->transaction;

        $expectedTransaction = [
            'amount' => '12.34',
            'usBankAccountDetails' => [
                'achMandate' => [
                    'text' => self::ACH_MANDATE_TEXT,
                ],
                'accountHolderName' => 'Dan Schulman',
                'last4' => '1234',
                'routingNumber' => '021000021',
                'accountType' => 'checking',
            ]
        ];

        $this->assertNotNull($transaction->id);
        $this->assertEquals($expectedTransaction['amount'], $transaction->amount);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['achMandate']['text'], $transaction->usBankAccount->achMandate->text);
        $this->assertInstanceOf('DateTime', $transaction->usBankAccount->achMandate->acceptedAt);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['accountHolderName'], $transaction->usBankAccount->accountHolderName);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['last4'], $transaction->usBankAccount->last4);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['routingNumber'], $transaction->usBankAccount->routingNumber);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['accountType'], $transaction->usBankAccount->accountType);
    }

    public function testVaultAndChargeWithInstantVerificationAccountValidation()
    {
        $gateway = $this->createUsBankGateway();

        $customerResult = $gateway->customer()->create([]);
        $this->assertTrue($customerResult->success);

        $nonce = $this->generateUsBankAccountNonceWithoutAchMandate();
        $mandateAcceptedAt = $this->createMandateDateTime();

        $paymentMethodResult = $gateway->paymentMethod()->create([
            'customerId' => $customerResult->customer->id,
            'paymentMethodNonce' => $nonce,
            'usBankAccount' => [
                'achMandateText' => self::ACH_MANDATE_TEXT,
                'achMandateAcceptedAt' => $mandateAcceptedAt->format('c')
            ],
            'options' => [
                'verificationMerchantAccountId' => Test\Helper::usBankMerchantAccount(),
                'usBankAccountVerificationMethod' => Braintree\Result\UsBankAccountVerification::INSTANT_VERIFICATION_ACCOUNT_VALIDATION,
            ]
        ]);
        $this->assertTrue($paymentMethodResult->success);

        $usBankAccount = $paymentMethodResult->paymentMethod;

        $expectedUsBankAccount = [
            'verifications' => [
                [
                    'verificationMethod' => Braintree\Result\UsBankAccountVerification::INSTANT_VERIFICATION_ACCOUNT_VALIDATION,
                    'status' => 'verified',
                ]
            ],
            'achMandate' => [
                'text' => self::ACH_MANDATE_TEXT,
            ]
        ];

        $this->assertEquals(1, count($usBankAccount->verifications));
        $verification = $usBankAccount->verifications[0];
        $this->assertEquals($expectedUsBankAccount['verifications'][0]['verificationMethod'], $verification->verificationMethod);
        $this->assertEquals($expectedUsBankAccount['verifications'][0]['status'], $verification->status);
        $this->assertEquals($expectedUsBankAccount['achMandate']['text'], $usBankAccount->achMandate->text);
        $this->assertInstanceOf('DateTime', $usBankAccount->achMandate->acceptedAt);

        $transactionResult = $gateway->transaction()->sale([
            'amount' => '12.34',
            'paymentMethodToken' => $usBankAccount->token,
            'merchantAccountId' => Test\Helper::usBankMerchantAccount(),
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        $this->assertTrue($transactionResult->success);

        $transaction = $transactionResult->transaction;

        $expectedTransaction = [
            'amount' => '12.34',
            'usBankAccountDetails' => [
                'token' => $usBankAccount->token,
                'achMandate' => [
                    'text' => self::ACH_MANDATE_TEXT,
                ],
                'last4' => '1234',
                'routingNumber' => '021000021',
                'accountType' => 'checking',
            ]
        ];

        $this->assertNotNull($transaction->id);
        $this->assertEquals($expectedTransaction['amount'], $transaction->amount);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['token'], $transaction->usBankAccount->token);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['achMandate']['text'], $transaction->usBankAccount->achMandate->text);
        $this->assertInstanceOf('DateTime', $transaction->usBankAccount->achMandate->acceptedAt);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['last4'], $transaction->usBankAccount->last4);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['routingNumber'], $transaction->usBankAccount->routingNumber);
        $this->assertEquals($expectedTransaction['usBankAccountDetails']['accountType'], $transaction->usBankAccount->accountType);
    }
}
