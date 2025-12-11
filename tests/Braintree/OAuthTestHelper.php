<?php

namespace Test\Braintree;

use Braintree;

class OAuthTestHelper
{
    public static function createGrant($gateway, $params)
    {
        $http = new Braintree\Http($gateway->config);
        $http->useClientCredentials();
        $response = $http->post('/oauth_testing/grants', ['grant' => $params]);
        return $response['grant']['code'];
    }

    public static function createCredentials($params)
    {
        $gateway = new Braintree\Gateway([
            'clientId' => $params['clientId'],
            'clientSecret' => $params['clientSecret']
        ]);

        $code = OAuthTestHelper::createGrant($gateway, [
            'merchant_public_id' => $params['merchantId'],
            'scope' => 'read_write'
        ]);

        $credentials = $gateway->oauth()->createTokenFromCode([
            'code' => $code,
            'scope' => 'read_write',
        ]);

        return $credentials;
    }

    public static function getMerchant($params = [])
    {
        $environment = 'development';

        $gateway = new Braintree\Gateway([
            'clientId' => "client_id\${$environment}\$integration_client_id",
            'clientSecret' => "client_secret\${$environment}\$integration_client_secret"
        ]);

        $code = self::createGrant($gateway, [
            'merchant_public_id' => 'partner_merchant_id',
            'scope' => 'read_write'
        ]);

        $result = $gateway->oauth()->createTokenFromCode([
            'code' => $code,
            'scope' => 'read_write'
        ]);

        $merchantGateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken
        ]);

        $maResult = $merchantGateway->merchantAccount()->all();

        $merchantAccounts = [];
        foreach ($maResult as $ma) {
            array_push($merchantAccounts, $ma);
        }

        $merchantObj = Braintree\Merchant::factory([
            'id' => 'partner_merchant_id',
            'merchantAccounts' => $merchantAccounts
        ]);

        return new Braintree\Result\Successful([
            'credentials' => $result->credentials,
            'merchant' => $merchantObj
        ]);
    }
}
