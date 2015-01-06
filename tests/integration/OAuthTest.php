<?php
require_once __DIR__ . '/../TestHelper.php';

class Braintree_OAuthTest extends PHPUnit_Framework_TestCase
{
    function testCreateAccessToken()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $result = $gateway->oauth()->createAccessToken(array(
            'code' => 'integration_oauth_auth_code_' . rand(0,299)
        ));

        $this->assertEquals(true, $result->success);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->token);
    }

    function testCreateAccessTokenFail()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $result = $gateway->oauth()->createAccessToken(array(
            'code' => 'bad_code'
        ));

        $this->assertEquals(false, $result->success);
        $credentials = $result->credentials;
        $this->assertEquals('invalid_grant', $credentials->error);
        $this->assertEquals('code not found', $credentials->message);
    }

    function testBuildConnectUrl()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $url = $gateway->oauth()->connectUrl(array(
            'merchantId' => 'foo_merchant_id',
            'redirectUri' => 'http://bar.example.com',
            'scopes' => 'read_write',
        ));

        $components = parse_url($url);
        $queryString = $components['query'];
        parse_str($queryString, $query);

        $this->assertEquals('localhost', $components['host']);
        $this->assertEquals('/oauth/connect', $components['path']);
        $this->assertEquals('foo_merchant_id', $query['merchant_id']);
        $this->assertEquals('client_id$development$integration_oauth_client_id', $query['client_id']);
        $this->assertEquals('http://bar.example.com', $query['redirect_uri']);
        $this->assertEquals('read_write', $query['scopes']);
    }

    function testBuildConnectUrlWithoutOptionalParams()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $url = $gateway->oauth()->connectUrl();

        $queryString = parse_url($url)['query'];
        parse_str($queryString, $query);

        $this->assertEquals('client_id$development$integration_oauth_client_id', $query['client_id']);
        $this->assertFalse(array_key_exists('merchant_id', $query));
        $this->assertFalse(array_key_exists('redirect_uri', $query));
        $this->assertFalse(array_key_exists('scopes', $query));
    }
}
