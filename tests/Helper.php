<?php

namespace Test;

require_once __DIR__ . '/Setup.php';

use DateTime;
use DateTimeZone;
use Braintree;

class Helper
{
    public static $valid_nonce_characters = 'bcdfghjkmnpqrstvwxyz23456789';

    public static function testMerchantConfig()
    {
        Braintree\Configuration::reset();

        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('test_merchant_id');
        Braintree\Configuration::publicKey('test_public_key');
        Braintree\Configuration::privateKey('test_private_key');
    }

    public static function integrationMerchantGateway()
    {
        return new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
    }

    public static function integration2MerchantConfig()
    {
        Braintree\Configuration::reset();

        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration2_merchant_id');
        Braintree\Configuration::publicKey('integration2_public_key');
        Braintree\Configuration::privateKey('integration2_private_key');
    }

    public static function advancedFraudKountIntegrationMerchantGateway()
    {
        return new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'advanced_fraud_integration_merchant_id',
            'publicKey' => 'advanced_fraud_integration_public_key',
            'privateKey' => 'advanced_fraud_integration_private_key'
        ]);
    }

    public static function fraudProtectionEnterpriseIntegrationMerchantGateway()
    {
        return new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'fraud_protection_enterprise_integration_merchant_id',
            'publicKey' => 'fraud_protection_enterprise_integration_public_key',
            'privateKey' => 'fraud_protection_enterprise_integration_private_key'
        ]);
    }

    public static function effortlessChargebackProtectionGateway()
    {
        return new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'fraud_protection_effortless_chargeback_protection_merchant_id',
            'publicKey' => 'effortless_chargeback_protection_public_key',
            'privateKey' => 'effortless_chargeback_protection_private_key'
        ]);
    }

    public static function duplicateCheckingMerchantGateway()
    {
        return new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'dup_checking_integration_merchant_id',
            'publicKey' => 'dup_checking_integration_public_key',
            'privateKey' => 'dup_checking_integration_private_key'
        ]);
    }

    public static function defaultMerchantAccountId()
    {
        return 'sandbox_credit_card';
    }

    public static function nonDefaultMerchantAccountId()
    {
        return 'sandbox_credit_card_non_default';
    }

    public static function nonDefaultSubMerchantAccountId()
    {
        return 'sandbox_sub_merchant_account';
    }

    public static function threeDSecureMerchantAccountId()
    {
        return 'three_d_secure_merchant_account';
    }

    public static function adyenMerchantAccountId()
    {
        return 'adyen_ma';
    }

    public static function fakeAmexDirectMerchantAccountId()
    {
        return 'fake_amex_direct_usd';
    }

    public static function fakeVenmoAccountMerchantAccountId()
    {
        return 'fake_first_data_venmo_account';
    }

    public static function fakeFirstDataMerchantAccountId()
    {
        return 'fake_first_data_merchant_account';
    }

    public static function usBankMerchantAccount()
    {
        return 'us_bank_merchant_account';
    }

    public static function anotherUsBankMerchantAccount()
    {
        return 'another_us_bank_merchant_account';
    }

    public static function cardProcessorBRLMerchantAccountId()
    {
        return 'card_processor_brl';
    }

    public static function suppressDeprecationWarnings()
    {
        set_error_handler("Test\Helper::_errorHandler", E_USER_NOTICE);
    }

    public static function _errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (preg_match('/^DEPRECATED/', $errstr) == 0) {
            trigger_error('Unknown error received: ' . $errstr, E_USER_ERROR);
        }
    }

    public static function includes($collection, $targetItem)
    {
        foreach ($collection as $item) {
            if ($item->id == $targetItem->id) {
                return true;
            }
        }
        return false;
    }

    public static function assertPrintable($object)
    {
        " " . $object;
    }

    public static function escrow($transactionId)
    {
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/transactions/' . $transactionId . '/escrow';
        $http->put($path);
    }

    public static function create3DSVerification($merchantAccountId, $params)
    {
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/three_d_secure/create_verification/' . $merchantAccountId;
        $response = $http->post($path, ['threeDSecureVerification' => $params]);
        return $response['threeDSecureVerification']['threeDSecureToken'];
    }

    public static function generate3DSNonce($params)
    {
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/three_d_secure/create_nonce/' . self::threeDSecureMerchantAccountId();
        $response = $http->post($path, $params);
        return $response['paymentMethodNonce']['nonce'];
    }

    public static function nowInEastern()
    {
        $eastern = new DateTimeZone('America/New_York');
        $now = new DateTime('now', $eastern);
        return $now->format('Y-m-d');
    }

    public static function decodedClientToken($params = [])
    {
        $encodedClientToken = Braintree\ClientToken::generate($params);
        return base64_decode($encodedClientToken);
    }

    public static function generateValidUsBankAccountNonce($accountNumber = '567891234')
    {
        $client_token = json_decode(Helper::decodedClientToken(), true);
        $url = $client_token['braintree_api']['url'] . '/graphql';
        $token = $client_token['braintree_api']['access_token'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_URL, $url);

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Braintree-Version: 2016-10-07';
        $headers[] = 'Authorization: Bearer ' . $token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $query =
            'mutation tokenizeUsBankAccount($input: TokenizeUsBankAccountInput!) {' .
            '   tokenizeUsBankAccount(input: $input) {' .
            '       paymentMethod {' .
            '           id' .
            '       }' .
            '   }' .
            '}';

        $variables = [
            'input' => [
                'usBankAccount' => [
                    'accountNumber' => $accountNumber,
                    'routingNumber' => '021000021',
                    'accountType' => 'CHECKING',
                    'billingAddress' => [
                        'streetAddress' => '123 Ave',
                        'state' => 'CA',
                        'city' => 'San Francisco',
                        'zipCode' => '94112'
                    ],
                    'individualOwner' => [
                        'firstName' => 'Dan',
                        'lastName' => 'Schulman'
                    ],
                    'achMandate' => 'cl mandate text'
                ]
            ]
        ];

        $requestBody = [
            'query' => $query,
            'variables' => $variables
        ];

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error_code = curl_errno($curl);
        curl_close($curl);
        $jsonResponse = json_decode($response, true);
        return $jsonResponse['data']['tokenizeUsBankAccount']['paymentMethod']['id'];
    }

    public static function generatePlaidUsBankAccountNonce()
    {
        $client_token = json_decode(Helper::decodedClientToken(), true);
        $url = $client_token['braintree_api']['url'] . '/graphql';
        $token = $client_token['braintree_api']['access_token'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_URL, $url);

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Braintree-Version: 2016-10-07';
        $headers[] = 'Authorization: Bearer ' . $token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $query =
            'mutation tokenizeUsBankLogin($input: TokenizeUsBankLoginInput!) {' .
            '   tokenizeUsBankLogin(input: $input) {' .
            '       paymentMethod {' .
            '           id' .
            '       }' .
            '   }' .
            '}';

        $variables = [
            'input' => [
                'usBankLogin' => [
                    'publicToken' => 'good',
                    'accountId' => 'plaid_account_id',
                    'accountType' => 'CHECKING',
                    'billingAddress' => [
                        'streetAddress' => '123 Ave',
                        'state' => 'CA',
                        'city' => 'San Francisco',
                        'zipCode' => '94112'
                    ],
                    'individualOwner' => [
                        'firstName' => 'Dan',
                        'lastName' => 'Schulman'
                    ],
                    'achMandate' => 'cl mandate text'
                ]
            ]
        ];

        $requestBody = [
            'query' => $query,
            'variables' => $variables
        ];

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error_code = curl_errno($curl);
        curl_close($curl);
        $jsonResponse = json_decode($response, true);
        return $jsonResponse['data']['tokenizeUsBankLogin']['paymentMethod']['id'];
    }

    public static function generateInvalidUsBankAccountNonce()
    {
        $valid_characters = str_split(self::$valid_nonce_characters);
        $nonce = 'tokenusbankacct';
        for ($i = 0; $i < 4; $i++) {
            $nonce = $nonce . '_';
            for ($j = 0; $j < 6; $j++) {
                $t = rand(0, sizeof($valid_characters) - 1);
                $nonce = $nonce . $valid_characters[$t];
            }
        }
        return $nonce . "_xxx";
    }

    public static function sampleNotificationFromXml($xml)
    {
        $config = Helper::integrationMerchantGateway()->config;
        $payload = base64_encode($xml) . "\n";
        $publicKey = $config->getPublicKey();
        $sha = Braintree\Digest::hexDigestSha1($config->getPrivateKey(), $payload);
        $signature = $publicKey . "|" . $sha;

        return [
            'bt_signature' => $signature,
            'bt_payload' => $payload
        ];
    }
}
