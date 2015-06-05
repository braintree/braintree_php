<?php namespace Braintree\Tests\Integration;

use Braintree\Configuration;
use Braintree\Http;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class HttpTest extends \PHPUnit_Framework_TestCase
{
    function testProductionSSL()
    {
        try {
            Configuration::environment('production');
            $this->setExpectedException('\Braintree\Exception\Authentication');
            $http = new Http(Configuration::$global);
            $http->get('/');
        } catch (\Exception $e) {
            Configuration::environment('development');
            throw $e;
        }
        Configuration::environment('development');
    }

    function testSandboxSSL()
    {
        try {
            Configuration::environment('sandbox');
            $this->setExpectedException('\Braintree\Exception\Authentication');
            $http = new Http(Configuration::$global);
            $http->get('/');
        } catch (\Exception $e) {
            Configuration::environment('development');
            throw $e;
        }
        Configuration::environment('development');
    }

    function testSslError()
    {
        try {
            Configuration::environment('sandbox');
            $this->setExpectedException('\Braintree\Exception\SSLCertificate');
            $http = new Http(Configuration::$global);
            //ip address of api.braintreegateway.com
            $http->_doUrlRequest('get', '204.109.13.121');
        } catch (\Exception $e) {
            Configuration::environment('development');
            throw $e;
        }
        Configuration::environment('development');
    }

    function testAuthorizationWithConfig()
    {
        $config = new Configuration(array(
            'environment' => 'development',
            'merchant_id' => 'integration_merchant_id',
            'publicKey'   => 'badPublicKey',
            'privateKey'  => 'badPrivateKey'
        ));

        $http = new Http($config);
        $result = $http->_doUrlRequest('GET', $config->baseUrl() . '/merchants/integration_merchant_id/customers');
        $this->assertEquals(401, $result['status']);
    }
}
