<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class HttpTest extends Setup
{
    public function setUp(): void
    {
        parent::setUp();

        Braintree\Configuration::environment('development');
        Braintree\Configuration::sslVersion(null);
        Braintree\Configuration::acceptGzipEncoding(false);
    }

    public function testProductionSSL()
    {
        try {
            Braintree\Configuration::environment('production');
            $this->expectException('Braintree\Exception');
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->get('/');
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testSandboxSSL()
    {
        try {
            Braintree\Configuration::environment('sandbox');
            $this->expectException('Braintree\Exception');
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->get('/');
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testSandboxSSLWithExplicitVersionSet()
    {
        try {
            Braintree\Configuration::environment('sandbox');
            Braintree\Configuration::sslVersion(6);
            $this->expectException('Braintree\Exception');
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->get('/');
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testSandboxSSLFailsWithIncompatibleSSLVersion()
    {
        $this->expectException('Braintree\Exception\Connection', null, 35);

        Braintree\Configuration::environment('sandbox');
        Braintree\Configuration::sslVersion(3);
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $http->_doUrlRequest('get', '/malformed_url');
    }

    public function testSslError()
    {
        $this->expectException('Braintree\Exception\Connection', null, 35);

        Braintree\Configuration::environment('sandbox');
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $http->_doUrlRequest('get', '/malformed_url');
    }

    public function testAcceptGzipEncodingSetFalse()
    {
        Braintree\Configuration::acceptGzipEncoding(false);
        try {
            $result = Braintree\Customer::create([
                'firstName' => 'Mike',
                'lastName' => 'Jones',
                'company' => 'Jones Co.',
                'email' => 'mike.jones@example.com',
                'phone' => '419.555.1234',
                'fax' => '419.555.1235',
                'website' => 'http://example.com'
                ]);
            $this->assertEquals(true, $result->success);
            $customer = $result->customer;
            $this->assertEquals('Mike', $customer->firstName);
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testAcceptGzipEncodingSetToTrue()
    {
        Braintree\Configuration::acceptGzipEncoding(true);
        try {
            $result = Braintree\Customer::create([
                'firstName' => 'Mike',
                'lastName' => 'Jones',
                'company' => 'Jones Co.',
                'email' => 'mike.jones@example.com',
                'phone' => '419.555.1234',
                'fax' => '419.555.1235',
                'website' => 'http://example.com'
                ]);
            $this->assertEquals(true, $result->success);
            $customer = $result->customer;
            $this->assertEquals('Mike', $customer->firstName);
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testAuthorizationWithConfig()
    {
        $config = new Braintree\Configuration([
            'environment' => 'development',
            'merchant_id' => 'integration_merchant_id',
            'publicKey' => 'badPublicKey',
            'privateKey' => 'badPrivateKey'
        ]);

        $http = new Braintree\Http($config);
        $result = $http->_doUrlRequest('GET', $config->baseUrl() . '/merchants/integration_merchant_id/customers');
        $this->assertEquals(401, $result['status']);
    }

    public function testPostMultiPartUploadsFileSuccessfully()
    {
        $config = Braintree\Configuration::$global;
        $http = new Braintree\Http($config);

        $path = '/merchants/integration_merchant_id/document_uploads';
        $params = [
            'document_upload[kind]' => 'evidence_document'
        ];
        $file = fopen(dirname(__DIR__) . '/fixtures/bt_logo.png', 'rb');
        $response = $http->postMultipart($path, $params, $file);

        $this->assertEquals('image/png', $response['documentUpload']['contentType']);
        $this->assertNotNull($response['documentUpload']['id']);
    }
}
