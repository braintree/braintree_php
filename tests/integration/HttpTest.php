<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class HttpTest extends Setup
{
    public function testProductionSSL()
    {
        try {
            Braintree\Configuration::environment('production');
            $this->setExpectedException('Braintree\Exception\Authentication');
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->get('/');
        } catch (Braintree\Exception $e) {
            Braintree\Configuration::environment('development');
            throw $e;
        }
        Braintree\Configuration::environment('development');
    }

    public function testSandboxSSL()
    {
        try {
            Braintree\Configuration::environment('sandbox');
            $this->setExpectedException('Braintree\Exception\Authentication');
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->get('/');
        } catch (Braintree\Exception $e) {
            Braintree\Configuration::environment('development');
            throw $e;
        }
        Braintree\Configuration::environment('development');
    }

    public function testSslError()
    {
        try {
            Braintree\Configuration::environment('sandbox');
            $this->setExpectedException('Braintree\Exception\SSLCertificate');
            $http = new Braintree\Http(Braintree\Configuration::$global);
            //ip address of api.braintreegateway.com
            $http->_doUrlRequest('get', '204.109.13.121');
        } catch (Braintree\Exception $e) {
            Braintree\Configuration::environment('development');
            throw $e;
        }
        Braintree\Configuration::environment('development');
    }

    public function testAuthorizationWithConfig()
    {
        $config = new Braintree\Configuration(array(
            'environment' => 'development',
            'merchant_id' => 'integration_merchant_id',
            'publicKey' => 'badPublicKey',
            'privateKey' => 'badPrivateKey'
        ));

        $http = new Braintree\Http($config);
        $result = $http->_doUrlRequest('GET', $config->baseUrl() . '/merchants/integration_merchant_id/customers');
        $this->assertEquals(401, $result['status']);
    }
}
