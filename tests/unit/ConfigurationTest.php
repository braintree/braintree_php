<?php namespace Braintree\Tests\Unit;

use Braintree\Configuration;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    function setup()
    {
        Configuration::reset();
        $this->config = new Configuration();
    }

    function teardown()
    {
        Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');
    }

    function testConstructWithArrayOfCredentials()
    {
        $config = new Configuration(array(
            'environment' => 'sandbox',
            'merchantId'  => 'sandbox_merchant_id',
            'publicKey'   => 'sandbox_public_key',
            'privateKey'  => 'sandbox_private_key'
        ));

        $this->assertEquals('sandbox', $config->getEnvironment());
        $this->assertEquals('sandbox_merchant_id', $config->getMerchantId());
    }

    function testSetValidEnvironment()
    {
        Configuration::environment('sandbox');
        $this->assertEquals('sandbox', Configuration::environment());
        Configuration::reset();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage "invalid" is not a valid environment.
     */
    function testSetInvalidEnvironment()
    {
        Configuration::environment('invalid');
        Configuration::reset();
    }

    function testMerchantPath()
    {
        $this->config->setMerchantId('abc123');
        $mp = $this->config->merchantPath();
        $this->assertEquals('/merchants/abc123', $mp);
    }

    function testCaFile()
    {
        $this->config->setEnvironment('development');
        $this->setExpectedException('\Braintree\Exception\SSLCaFileNotFound');
        $this->config->caFile('/does/not/exist/');
    }

    function testSSLOn()
    {
        $this->config->setEnvironment('development');
        $on = $this->config->sslOn();
        $this->assertFalse($on);

        $this->config->setEnvironment('sandbox');
        $on = $this->config->sslOn();
        $this->assertTrue($on);

        $this->config->setEnvironment('production');
        $on = $this->config->sslOn();
        $this->assertTrue($on);
    }

    function testPortNumber()
    {
        $this->config->setEnvironment('development');
        $pn = $this->config->portNumber();
        $this->assertEquals(getenv("GATEWAY_PORT") ? getenv("GATEWAY_PORT") : 3000, $pn);

        $this->config->setEnvironment('sandbox');
        $pn = $this->config->portNumber();
        $this->assertEquals(443, $pn);

        $this->config->setEnvironment('production');
        $pn = $this->config->portNumber();
        $this->assertEquals(443, $pn);
    }


    function testProtocol()
    {
        $this->config->setEnvironment('development');
        $p = $this->config->protocol();
        $this->assertEquals('http', $p);

        $this->config->setEnvironment('sandbox');
        $p = $this->config->protocol();
        $this->assertEquals('https', $p);

        $this->config->setEnvironment('production');
        $p = $this->config->protocol();
        $this->assertEquals('https', $p);
    }

    function testServerName()
    {
        $this->config->setEnvironment('development');
        $sn = $this->config->serverName();
        $this->assertEquals('localhost', $sn);

        $this->config->setEnvironment('sandbox');
        $sn = $this->config->serverName();
        $this->assertEquals('api.sandbox.braintreegateway.com', $sn);

        $this->config->setEnvironment('production');
        $sn = $this->config->serverName();
        $this->assertEquals('api.braintreegateway.com', $sn);
    }

    function testAuthUrl()
    {
        $this->config->setEnvironment('development');
        $authUrl = $this->config->authUrl();
        $this->assertEquals('http://auth.venmo.dev:9292', $authUrl);

        $this->config->setEnvironment('qa');
        $authUrl = $this->config->authUrl();
        $this->assertEquals('https://auth.qa.venmo.com', $authUrl);

        $this->config->setEnvironment('sandbox');
        $authUrl = $this->config->authUrl();
        $this->assertEquals('https://auth.sandbox.venmo.com', $authUrl);

        $this->config->setEnvironment('production');
        $authUrl = $this->config->authUrl();
        $this->assertEquals('https://auth.venmo.com', $authUrl);
    }

    function testBaseUrl()
    {
        $this->config->setEnvironment('development');
        $bu = $this->config->baseUrl();
        $this->assertEquals('http://localhost:' . $this->config->portNumber(), $bu);

        $fakeConfig = $this->getMockBuilder('\Braintree\Configuration')->setMethods(array('portNumber'))->getMock();
        $fakeConfig->expects($this->once())->method('portNumber')->will($this->returnValue(80));
        $fakeConfig->setEnvironment('development');
        $bu = $fakeConfig->baseUrl();
        $this->assertEquals('http://localhost', $bu);

        $this->config->setEnvironment('qa');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://gateway.qa.braintreepayments.com', $bu);

        $this->config->setEnvironment('sandbox');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://api.sandbox.braintreegateway.com', $bu);

        $this->config->setEnvironment('production');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://api.braintreegateway.com', $bu);
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage environment needs to be set.
     */
    function testValidateEmptyEnvironment()
    {
        //Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');

        Configuration::$global->assertHasAccessTokenOrKeys();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage merchantId needs to be set.
     */
    function testMerchantId()
    {
        Configuration::environment('development');
        //Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');

        Configuration::$global->assertHasAccessTokenOrKeys();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage publicKey needs to be set.
     */
    function testPublicKey()
    {
        Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        //Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');

        Configuration::$global->assertHasAccessTokenOrKeys();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage privateKey needs to be set.
     */
    function testPrivateKey()
    {
        Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        //Configuration::privateKey('integration_private_key');

        Configuration::$global->assertHasAccessTokenOrKeys();
    }

    function testValidWithOAuthClientCredentials()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));

        $config->assertHasClientCredentials();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage clientSecret needs to be set.
     */
    function testInvalidWithOAuthClientCredentials()
    {
        $config = new Configuration(array(
            'clientId' => 'client_id$development$integration_client_id'
        ));

        $config->assertHasClientCredentials();
    }

    function testDetectEnvironmentFromClientId()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));

        $this->assertEquals('development', $config->getEnvironment());
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage Mismatched credential environments: clientId environment is sandbox and clientSecret environment is development
     */
    function testDetectEnvironmentFromClientIdFail()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_id$sandbox$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage Value passed for clientId is not a clientId
     */
    function testClientIdTypeFail()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_secret$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));
    }

    function testValidWithAccessToken()
    {
        $config = new Configuration(array(
            'accessToken' => 'access_token$development$integration_merchant_id$integration_access_token',
        ));

        $config->assertHasAccessTokenOrKeys();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage Value passed for accessToken is not an accessToken
     */
    function testInvalidAccessTokenType()
    {
        $config = new Configuration(array(
            'accessToken' => 'client_id$development$integration_merchant_id$integration_access_token',
        ));
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage Incorrect accessToken syntax. Expected: type$environment$merchant_id$token
     */
    function testInvalidAccessTokenSyntax()
    {
        $config = new Configuration(array(
            'accessToken' => 'client_id$development$integration_client_id',
        ));
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage "invalid" is not a valid environment.
     */
    function testInvalidAccessTokenEnvironment()
    {
        $config = new Configuration(array(
            'accessToken' => 'access_token$invalid$integration_merchant_id$integration_access_token',
        ));
    }


    function testValidWithOAuthClientCredentialsAndAccessToken()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'accessToken'  => 'access_token$development$integration_merchant_id$integration_access_token',
        ));

        $config->assertHasClientCredentials();
        $config->assertHasAccessTokenOrKeys();
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage Mismatched credential environments: clientId environment is development and accessToken environment is sandbox
     */
    function testInvalidEnvironmentWithOAuthClientCredentialsAndAccessToken()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'accessToken'  => 'access_token$sandbox$integration_merchant_id$integration_access_token',
        ));
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage Cannot mix OAuth credentials (clientId, clientSecret, accessToken) with key credentials (publicKey, privateKey, environment, merchantId).
     */
    function testCannotMixKeysWithOAuthCredentials()
    {
        $config = new Configuration(array(
            'clientId'     => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'environment'  => 'development',
            'merchantId'   => 'integration_merchant_id',
            'publicKey'    => 'integration_public_key',
            'privateKey'   => 'integration_private_key'
        ));
    }
}
