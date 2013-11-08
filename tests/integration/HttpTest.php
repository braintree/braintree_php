<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_HttpTest extends PHPUnit_Framework_TestCase
{
    function testProductionSSL()
    {
        try {
            Braintree_Configuration::environment('production');
            $this->setExpectedException('Braintree_Exception_Authentication');
            Braintree_Http::get('/');
        } catch (Exception $e) {
            Braintree_Configuration::environment('development');
            throw $e;
        }
        Braintree_Configuration::environment('development');
    }

    function testSandboxSSL()
    {
        try {
            Braintree_Configuration::environment('sandbox');
            $this->setExpectedException('Braintree_Exception_Authentication');
            Braintree_Http::get('/');
        } catch (Exception $e) {
            Braintree_Configuration::environment('development');
            throw $e;
        }
        Braintree_Configuration::environment('development');
    }

    function testSslError()
    {
        try {
            Braintree_Configuration::environment('sandbox');
            $this->setExpectedException('Braintree_Exception_SSLCertificate');
            //ip address of api.braintreegateway.com
            Braintree_Http::_doUrlRequest('get', '204.109.13.121');
        } catch (Exception $e) {
            Braintree_Configuration::environment('development');
            throw $e;
        }
        Braintree_Configuration::environment('development');
    }
}
?>
