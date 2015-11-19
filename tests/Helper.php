<?php
namespace Test;

require_once __DIR__ . '/Setup.php';

use DateTime;
use DateTimeZone;
use Braintree;

class Helper
{
    public static function testMerchantConfig()
    {
        Braintree\Configuration::reset();

        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('test_merchant_id');
        Braintree\Configuration::publicKey('test_public_key');
        Braintree\Configuration::privateKey('test_private_key');
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

    public static function fakeAmexDirectMerchantAccountId()
    {
        return 'fake_amex_direct_usd';
    }

    public static function fakeVenmoAccountMerchantAccountId()
    {
        return 'fake_first_data_venmo_account';
    }

    public static function createViaTr($regularParams, $trParams)
    {
        $trData = Braintree\TransparentRedirect::transactionData(
            array_merge($trParams, ["redirectUrl" => "http://www.example.com"])
        );
        return self::submitTrRequest(
            Braintree\TransparentRedirect::url(),
            $regularParams,
            $trData
        );
    }

    public static function submitTrRequest($url, $regularParams, $trData)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HEADER, true);
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array_merge($regularParams, ['tr_data' => $trData])));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        preg_match('/Location: .*\?(.*)/i', $response, $match);
        return trim($match[1]);
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
        foreach ($collection AS $item) {
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

    public static function nowInEastern()
    {
        $eastern = new DateTimeZone('America/New_York');
        $now = new DateTime('now', $eastern);
        return $now->format('Y-m-d');
    }

    public static function decodedClientToken($params=[]) {
        $encodedClientToken = Braintree\ClientToken::generate($params);
        return base64_decode($encodedClientToken);
    }
}
