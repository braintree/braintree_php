<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_HttpTest extends PHPUnit_Framework_TestCase
{
    function testProductionSSL()
    {
        Braintree_Configuration::environment('production');
        $this->setExpectedException('Braintree_Exception_Authentication');
        Braintree_Http::get('/');
        Braintree_Configuration::environment('development');
    }

    function testSandboxSSL()
    {
        Braintree_Configuration::environment('sandbox');
        $this->setExpectedException('Braintree_Exception_Authentication');
        Braintree_Http::get('/');
        Braintree_Configuration::environment('development');
    }
}
?>
