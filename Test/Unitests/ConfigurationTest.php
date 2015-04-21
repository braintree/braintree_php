<?php

namespace Test\Unitests;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class ConfigurationTest extends Setup
{
    public function setup()
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
        $config = new Braintree\Configuration(array(
            'environment' => 'sandbox',
            'merchantId' => 'sandbox_merchant_id',
            'publicKey' => 'sandbox_public_key',
            'privateKey' => 'sandbox_private_key',
        ));

        $this->assertEquals('sandbox', $config->getEnvironment());
        $this->assertEquals('sandbox_merchant_id', $config->getMerchantId());
    }

    public function testSetValidEnvironment()
    {
        Braintree\Configuration::environment('sandbox');
        $this->assertEquals('sandbox', Braintree\Configuration::environment());
        Braintree\Configuration::reset();
    }

    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage "invalid" is not a valid environment.
     */
    public function testSetInvalidEnvironment()
    {
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
        $this->setExpectedException('Braintree\Exception\SSLCaFileNotFound');
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
        $this->assertEquals(getenv('GATEWAY_PORT') ? getenv('GATEWAY_PORT') : 3000, $pn);

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

    public function testAuthUrl()
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

    public function testBaseUrl()
    {
        $this->config->setEnvironment('sandbox');
        $bu = $this->config->baseUrl();
        $this->assertEquals('https://api.sandbox.braintreegateway.com:443', $bu);
    }

    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage environment needs to be set.
     */
    public function testValidateEmptyEnvironment()
    {
        //Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        Braintree\Configuration::$global->assertValid();
    }
    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage merchantId needs to be set.
     */
    public function testMerchantId()
    {
        Braintree\Configuration::environment('development');
        //Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        Braintree\Configuration::$global->assertValid();
    }
    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage publicKey needs to be set.
     */
    public function testPublicKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        //Braintree\Configuration::publicKey('integration_public_key');
        Braintree\Configuration::privateKey('integration_private_key');

        Braintree\Configuration::$global->assertValid();
    }
    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage privateKey needs to be set.
     */
    public function testPrivateKey()
    {
        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('integration_public_key');
        //Braintree\Configuration::privateKey('integration_private_key');

        Braintree\Configuration::$global->assertValid();
    }
}
