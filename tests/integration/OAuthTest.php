<?php
require_once __DIR__ . '/../TestHelper.php';

class Braintree_OAuthTest extends PHPUnit_Framework_TestCase
{
    /**
    * @expectedException Braintree_Exception_Configuration
    * @expectedExceptionMessage clientSecret needs to be set.
    */
    function testAssertsHasCredentials()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id'
        ));
        $gateway->oauth()->createAccessToken(array(
            'code' => 'integration_oauth_auth_code_' . rand(0,299)
        ));
    }

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
            'state' => 'baz_state',
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
        $this->assertEquals('baz_state', $query['state']);
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
        $this->assertArrayNotHasKey('merchant_id', $query);
        $this->assertArrayNotHasKey('redirect_uri', $query);
        $this->assertArrayNotHasKey('scopes', $query);
    }
}
