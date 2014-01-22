<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_ConfigurationTest extends PHPUnit_Framework_TestCase
{
    function setup()
    {
        Braintree_Configuration::reset();
    }

    function teardown()
    {
        Braintree_Configuration::environment('development');
        Braintree_Configuration::merchantId('integration_merchant_id');
        Braintree_Configuration::publicKey('integration_public_key');
        Braintree_Configuration::privateKey('integration_private_key');
    }

    function testSetValidEnvironment()
    {
        Braintree_Configuration::environment('sandbox');
        $this->assertEquals('sandbox', Braintree_Configuration::environment());
        Braintree_Configuration::reset();
    }

     /**
     * @expectedException Braintree_Exception_Configuration
     * @expectedExceptionMessage environment needs to be set
     */
    function testSetInvalidEnvironment()
    {
        Braintree_Configuration::environment('invalid');
        Braintree_Configuration::reset();
    }

     /**
     * @expectedException Braintree_Exception_Configuration
     * @expectedExceptionMessage environment needs to be set
     */
    function testValidateEmptyEnvironment()
    {
        // try to get environment without setting it first
        Braintree_Configuration::environment();
    }

    function testMerchantPath()
    {
        Braintree_Configuration::merchantId('abc123');
        $mp = Braintree_Configuration::merchantPath();
        $this->assertEquals('/merchants/abc123', $mp);
        Braintree_Configuration::reset();
    }

    function testCaFile()
    {
        Braintree_Configuration::environment('development');
        $this->setExpectedException('Braintree_Exception_SSLCaFileNotFound');
        Braintree_Configuration::caFile('/does/not/exist/');
    }

    function testSSLOn()
    {
        Braintree_Configuration::environment('development');
        $on = Braintree_Configuration::sslOn();
        $this->assertFalse($on);

        Braintree_Configuration::environment('sandbox');
        $on = Braintree_Configuration::sslOn();
        $this->assertTrue($on);

        Braintree_Configuration::environment('production');
        $on = Braintree_Configuration::sslOn();
        $this->assertTrue($on);

        Braintree_Configuration::reset();
    }

    function testPortNumber()
    {
        Braintree_Configuration::environment('development');
        $pn = Braintree_Configuration::portNumber();
        $this->assertEquals(getenv("GATEWAY_PORT") ? getenv("GATEWAY_PORT") : 3000, $pn);

        Braintree_Configuration::environment('sandbox');
        $pn = Braintree_Configuration::portNumber();
        $this->assertEquals(443, $pn);

        Braintree_Configuration::environment('production');
        $pn = Braintree_Configuration::portNumber();
        $this->assertEquals(443, $pn);

        Braintree_Configuration::reset();
    }


    function testProtocol()
    {
        Braintree_Configuration::environment('development');
        $p = Braintree_Configuration::protocol();
        $this->assertEquals('http', $p);

        Braintree_Configuration::environment('sandbox');
        $p = Braintree_Configuration::protocol();
        $this->assertEquals('https', $p);

        Braintree_Configuration::environment('production');
        $p = Braintree_Configuration::protocol();
        $this->assertEquals('https', $p);

        Braintree_Configuration::reset();
    }

    function testServerName()
    {
        Braintree_Configuration::environment('development');
        $sn = Braintree_Configuration::serverName();
        $this->assertEquals('localhost', $sn);

        Braintree_Configuration::environment('sandbox');
        $sn = Braintree_Configuration::serverName();
        $this->assertEquals('api.sandbox.braintreegateway.com', $sn);

        Braintree_Configuration::environment('production');
        $sn = Braintree_Configuration::serverName();
        $this->assertEquals('api.braintreegateway.com', $sn);

        Braintree_Configuration::reset();
    }

    function testAuthUrl()
    {
        Braintree_Configuration::environment('development');
        $authUrl = Braintree_Configuration::authUrl();
        $this->assertEquals('http://auth.venmo.dev:9292', $authUrl);

        Braintree_Configuration::environment('qa');
        $authUrl = Braintree_Configuration::authUrl();
        $this->assertEquals('https://auth.qa.venmo.com', $authUrl);

        Braintree_Configuration::environment('sandbox');
        $authUrl = Braintree_Configuration::authUrl();
        $this->assertEquals('https://auth.sandbox.venmo.com', $authUrl);

        Braintree_Configuration::environment('production');
        $authUrl = Braintree_Configuration::authUrl();
        $this->assertEquals('https://auth.venmo.com', $authUrl);

        Braintree_Configuration::reset();
    }

    function testmerchantUrl()
    {
        Braintree_Configuration::merchantId('abc123');
        Braintree_Configuration::environment('sandbox');
        $mu = Braintree_Configuration::merchantUrl();
        $this->assertEquals('https://api.sandbox.braintreegateway.com:443/merchants/abc123', $mu);

        Braintree_Configuration::reset();
    }

    function testBaseUrl()
    {
        Braintree_Configuration::environment('sandbox');
        $bu = Braintree_Configuration::baseUrl();
        $this->assertEquals('https://api.sandbox.braintreegateway.com:443', $bu);

        Braintree_Configuration::reset();
    }
     /**
     * @expectedException Braintree_Exception_Configuration
     * @expectedExceptionMessage merchantId needs to be set.
     */
    function testMerchantId()
    {
        $mi = Braintree_Configuration::merchantId();
    }
     /**
     * @expectedException Braintree_Exception_Configuration
     * @expectedExceptionMessage publicKey needs to be set.
     */
    function testPublicKey()
    {
        $pk = Braintree_Configuration::publicKey();
    }
     /**
     * @expectedException Braintree_Exception_Configuration
     * @expectedExceptionMessage privateKey needs to be set.
     */
    function testPrivateKey()
    {
        $pk = Braintree_Configuration::privateKey();
    }
}
