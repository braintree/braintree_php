<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class HttpTest extends Setup
{
    public function setUp(): void {
        parent::setUp();
        Braintree\Configuration::environment('development');
        Braintree\Configuration::sslVersion(null);
    }

    public function testMalformedNoSsl()
    {
        try {
            $this->expectException('Braintree\Exception\Connection', null, 3);
            $http = new Braintree\Http(Braintree\Configuration::$global);
            $http->_doUrlRequest('get', '/a_malformed_url');
        } catch (Braintree\Exception $e) {
            throw $e;
        }
    }

    public function testMalformedUrlUsingSsl()
    {
        $this->ExpectException('Braintree\Exception\Connection', null, 3);
 
        Braintree\Configuration::environment('sandbox');
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $http->_doUrlRequest('get', '/a_malformed_url_using_ssl');
    }

    public function testOlderSSLVersionsError()
    {
        $this->expectException('Braintree\Exception\Connection');

        Braintree\Configuration::environment('sandbox');
        Braintree\Configuration::sslVersion(3);
        $http = new Braintree\Http(Braintree\Configuration::$global);
        $http->get('/');
    }
}
