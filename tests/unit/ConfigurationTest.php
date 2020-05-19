<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ConfigurationTest extends Setup
{
    public function setUp()
    {
        Braintree\Configuration::reset();
        $this->config = new Braintree\Configuration();
    }

    public function teardown()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');
    }

    public function testConstructWithArrayOfCredentials()
    {
        $config = new Braintree\Configuration([
            'environment' => 'sandbox',
            'merchantId' => 'sandbox_merchant_id',
            'publicKey' => 'sandbox_public_key',
            'privateKey' => 'sandbox_private_key',
            'timeout' => 120,
            'acceptGzipEncoding' => false,
        ]);

        $this->assertEquals('sandbox', $config->getEnvironment());
        $this->assertEquals('sandbox_merchant_id', $config->getMerchantId());
        $this->assertEquals(120, $config->getTimeout());
        $this->assertFalse($config->getAcceptGzipEncoding());
    }

    function testConstructWithProxyServerAndSslVersionAttributes()
    {
        $config = new Braintree\Configuration([
            'environment' => 'sandbox',
            'merchantId' => 'sandbox_merchant_id',
            'publicKey' => 'sandbox_public_key',
            'privateKey' => 'sandbox_private_key',
            'proxyHost' => 'example.com',
            'proxyPort' => '5678',
            'proxyType' => 'foo',
            'proxyUser' => 'username',
            'proxyPassword' => 'password',
            'sslVersion' => '2',
        ]);
        $this->assertEquals('example.com', $config->getProxyHost());
        $this->assertEquals('5678', $config->getProxyPort());
        $this->assertEquals('foo', $config->getProxyType());
        $this->assertEquals('username', $config->getProxyUser());
        $this->assertEquals('password', $config->getProxyPassword());
        $this->assertEquals('2', $config->getSslVersion());
    }

    public function testAssertGlobalHasAccessTokenOrKeys()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        try {
            Braintree\Configuration::assertGlobalHasAccessTokenOrKeys();
        } catch (Exception $notExpected) {
            $this->fail();
        }

        $this->assertTrue(TRUE);
    }

    public function testAssertGlobalHasAccessTokenOrKeysWithoutPublicKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'Configuration::publicKey needs to be set');

        Braintree\Configuration::assertGlobalHasAccessTokenOrKeys();
    }

    public function testSetValidEnvironment()
    {
        Braintree\Configuration::environment('sandbox');
        $this->assertEquals('sandbox', Braintree\Configuration::environment());
        Braintree\Configuration::reset();
    }

    public function testSetInvalidEnvironment()
    {
        $this->expectException('Braintree\Exception\Configuration', '"invalid" is not a valid environment.');
        Braintree\Configuration::environment('invalid');
        Braintree\Configuration::reset();
    }

    public function testMerchantPath()
    {
        $this->config->setMerchantId('abc123');
        $mp = $this->config->merchantPath();
        $this->assertEquals('/merchants/abc123', $mp);
    }

    public function testCaFile()
    {
        $this->config->setEnvironment('development');
        $this->expectException('Braintree\Exception\SSLCaFileNotFound');
        $this->config->caFile('/does/not/exist/');
    }

    public function testSSLOn()
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

    public function testPortNumber()
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


    public function testProtocol()
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

    public function testServerName()
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

    public function testBaseUrl()
    {
        $this->config->setEnvironment('development');
        $bu = $this->config->baseUrl();
        $this->assertEquals('http://localhost:' . $this->config->portNumber(), $bu);

        $this->config->setEnvironment('qa');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://gateway.qa.braintreepayments.com:443', $bu);

        $this->config->setEnvironment('sandbox');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://api.sandbox.braintreegateway.com:443', $bu);

        $this->config->setEnvironment('production');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://api.braintreegateway.com:443', $bu);
    }

    function testProxyHost()
    {
        $this->config->proxyHost('example.com');
        $this->assertEquals('example.com', $this->config->proxyHost());
    }

    function testProxyPort()
    {
        $this->config->proxyPort('1234');
        $this->assertEquals('1234', $this->config->proxyPort());
    }

    function testProxyType()
    {
        $this->config->proxyType('MY_PROXY');
        $this->assertEquals('MY_PROXY', $this->config->proxyType());
    }

    function testInstanceProxyIsConfigured()
    {
        $config = new Braintree\Configuration([
            'proxyHost' => 'example.com',
            'proxyPort' => '5678',
        ]);

        $this->assertTrue($config->isUsingInstanceProxy());
    }

    function testProxyUser()
    {
        $this->config->proxyUser('user');
        $this->assertEquals('user', $this->config->proxyUser());
    }

    function testProxyPassword()
    {
        $this->config->proxyPassword('password');
        $this->assertEquals('password', $this->config->proxyPassword());
    }

    function testInstanceIsAuthenticatedProxy()
    {

        $config = new Braintree\Configuration([
            'proxyUser' => 'user',
            'proxyPassword' => 'password',
        ]);

        $this->assertTrue($config->isAuthenticatedInstanceProxy());
    }

    function testTimeout()
    {
        Braintree\Configuration::timeout(30);

        $this->assertEquals(30, Braintree\Configuration::timeout());
    }

    function testTimeoutDefaultsToSixty()
    {
        $this->assertEquals(60, Braintree\Configuration::timeout());
    }

    function testSslVersion()
    {
        $this->config->sslVersion(6);

        $this->assertEquals(6, $this->config->sslVersion());
    }

    function testSslVersionDefaultsToNull()
    {
        $this->assertEquals(null, $this->config->sslVersion());
    }

    public function testAcceptEncodingDefaultsTrue()
    {
        $this->assertTrue($this->config->acceptGzipEncoding());
    }

    public function testAcceptGzipEncoding()
    {
        $this->assertTrue($this->config->acceptGzipEncoding());
        $this->config->acceptGzipEncoding(false);
        $this->assertFalse($this->config->acceptGzipEncoding());
    }

    public function testValidateAbsentEnvironment()
    {
        //Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'environment needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testValidateEmptyStringEnvironment()
    {
        Braintree\Configuration::environment('');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'environment needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testAbsentMerchantId()
    {
        Braintree\Configuration::environment('development');
        //Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'merchantId needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testEmptyStringMerchantId()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'merchantId needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testAbsentPublicKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        //Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'publicKey needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testEmptyStringPublicKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('');
        Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'publicKey needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testAbsentPrivateKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        //Braintree\Configuration::privateKey('integration_private_key');

        $this->expectException('Braintree\Exception\Configuration', 'privateKey needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testEmptyStringPrivateKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('');

        $this->expectException('Braintree\Exception\Configuration', 'privateKey needs to be set');

        Braintree\Configuration::$global->assertHasAccessTokenOrKeys();
    }

    public function testValidWithOAuthClientCredentials()
    {
        $config = new Braintree\Configuration([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $this->assertNull($config->assertHasClientCredentials());
    }

    public function testInvalidWithOAuthClientCredentials()
    {
        $config = new Braintree\Configuration([
            'clientId' => 'client_id$development$integration_client_id'
        ]);

        $this->expectException('Braintree\Exception\Configuration', 'clientSecret needs to be passed');

        $config->assertHasClientCredentials();
    }

    public function testDetectEnvironmentFromClientId()
    {
        $config = new Braintree\Configuration([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);

        $this->assertEquals('development', $config->getEnvironment());
    }

    public function testDetectEnvironmentFromClientIdFail()
    {
        $this->expectException('Braintree\Exception\Configuration', 'Mismatched credential environments: clientId environment is sandbox and clientSecret environment is development');

        $config = new Braintree\Configuration([
            'clientId' => 'client_id$sandbox$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);
    }

    public function testClientIdTypeFail()
    {
        $this->expectException('Braintree\Exception\Configuration', 'Value passed for clientId is not a clientId');
        
        $config = new Braintree\Configuration([
            'clientId' => 'client_secret$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ]);
    }

    public function testValidWithAccessToken()
    {
        $config = new Braintree\Configuration([
            'accessToken' => 'access_token$development$integration_merchant_id$integration_access_token',
        ]);

        $this->assertNull($config->assertHasAccessTokenOrKeys());
    }

    public function testInvalidAccessTokenType()
    {
        $this->expectException('Braintree\Exception\Configuration', 'Value passed for accessToken is not an accessToken');
        
        $config = new Braintree\Configuration([
            'accessToken' => 'client_id$development$integration_merchant_id$integration_access_token',
        ]);
    }

    public function testInvalidAccessTokenSyntax()
    {
        $this->expectException('Braintree\Exception\Configuration', 'Incorrect accessToken syntax. Expected: type$environment$merchant_id$token');
        
        $config = new Braintree\Configuration([
            'accessToken' => 'client_id$development$integration_client_id',
        ]);
    }

    public function testInvalidAccessTokenEnvironment()
    {
        $this->expectException('Braintree\Exception\Configuration', '"invalid" is not a valid environment.');
        
        $config = new Braintree\Configuration([
            'accessToken' => 'access_token$invalid$integration_merchant_id$integration_access_token',
        ]);
    }

    public function testValidWithOAuthClientCredentialsAndAccessToken()
    {
        $config = new Braintree\Configuration([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'accessToken' => 'access_token$development$integration_merchant_id$integration_access_token',
        ]);

        $this->assertNull($config->assertHasClientCredentials());
        $this->assertNull($config->assertHasAccessTokenOrKeys());
    }

    public function testInvalidEnvironmentWithOAuthClientCredentialsAndAccessToken()
    {
        $this->expectException('Braintree\Exception\Configuration', 'Mismatched credential environments: clientId environment is development and accessToken environment is sandbox');
        
        $config = new Braintree\Configuration([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'accessToken' => 'access_token$sandbox$integration_merchant_id$integration_access_token',
        ]);
    }

    public function testCannotMixKeysWithOAuthCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration', 'Cannot mix OAuth credentials (clientId, clientSecret, accessToken) with key credentials (publicKey, privateKey, environment, merchantId)');
        
        $config = new Braintree\Configuration([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
    }
}
