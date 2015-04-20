<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_GatewayTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        Braintree_Configuration::reset();
    }

    public function teardown()
    {
        Braintree_Configuration::environment('development');
        Braintree_Configuration::merchantId('integration_merchant_id');
        Braintree_Configuration::publicKey('integration_public_key');
        Braintree_Configuration::privateKey('integration_private_key');
    }

    /**
     * @expectedException Braintree_Exception_Configuration
     * @expectedExceptionMessage merchantId needs to be set.
     */
    public function testConfigGetsAssertedValid()
    {
        Braintree_Configuration::environment('development');
        //Braintree_Configuration::merchantId('integration_merchant_id');
        Braintree_Configuration::publicKey('integration_public_key');
        Braintree_Configuration::privateKey('integration_private_key');

        new Braintree_Gateway(Braintree_Configuration::$global);
    }

    public function testConstructWithArrayOfCredentials()
    {
        $gateway = new Braintree_Gateway(array(
            'environment' => 'sandbox',
            'merchantId' => 'sandbox_merchant_id',
            'publicKey' => 'sandbox_public_key',
            'privateKey' => 'sandbox_private_key',
        ));

        $this->assertEquals('sandbox', $gateway->config->getEnvironment());
        $this->assertEquals('sandbox_merchant_id', $gateway->config->getMerchantId());
    }
}
