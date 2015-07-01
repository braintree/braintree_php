<?php
namespace Test\Braintree;

use Braintree;

class OAuthTestHelper
{
    public static function createGrant($gateway, $params)
    {
        $http = new Braintree\Http($gateway->config);
        $http->useClientCredentials();
        $response = $http->post('/oauth_testing/grants', array('grant' => $params));
        return $response['grant']['code'];
    }

    public static function createCredentials($params)
    {
        $gateway = new Braintree\Gateway(array(
            'clientId' => $params['clientId'],
            'clientSecret' => $params['clientSecret']
        ));

        $code = OAuthTestHelper::createGrant($gateway, array(
            'merchant_public_id' => $params['merchantId'],
            'scope' => 'read_write'
        ));

        $credentials = $gateway->oauth()->createTokenFromCode(array(
            'code' => $code,
            'scope' => 'read_write',
        ));

        return $credentials;
    }
}
