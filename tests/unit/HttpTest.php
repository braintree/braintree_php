<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class HttpTest extends Setup
{
    /**TODO LIST OF THINGS TO DO STILL
     * 1) Review changes, ensure we didn't break integration tests
     * 2) See if we need to broaden our exception
     * 3) See if we should check for other SSL related codes
     * 4) See if we should change these tests a bit to be more unique and less a copy and paste job
     */

    public function testMalformedNoSsl()
    {
        try {
            Braintree\Configuration::environment('development');
            $this->setExpectedException('Braintree\Exception\Connection', null, 3);
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->_doUrlRequest('get', '/a_malformed_url');
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testMalformedUrlUsingSsl()
    {
        try {
            Braintree\Configuration::environment('sandbox');
            $this->setExpectedException('Braintree\Exception\Connection', null, 3);
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->_doUrlRequest('get', '/a_malformed_url_using_ssl');
        } catch (Braintree\Exception $e) {
            Braintree\Configuration::environment('development');
            throw $e;
        }
        Braintree\Configuration::environment('development');
    }

    public function testSSLVersionError()
    {
        try {
            Braintree\Configuration::environment('sandbox');
            Braintree\Configuration::sslVersion(3);
            $this->setExpectedException('Braintree\Exception\SSLCertificate', null, 35);
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->get('/');
        } catch (Braintree\Exception $e) {
            Braintree\Configuration::environment('development');
            Braintree\Configuration::sslVersion(null);
            throw $e;
        }
        Braintree\Configuration::environment('development');
        Braintree\Configuration::sslVersion(null);
    }

    public function testGoodRequest()
    {
        Braintree\Configuration::environment('development');
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $http->_doUrlRequest('get', 'http://example.com');
    }
}