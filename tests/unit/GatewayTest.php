<?php namespace Braintree\Tests\Unit;

use Braintree\Configuration;
use Braintree\Gateway;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    function setup()
    {
        Configuration::reset();
    }

    function teardown()
    {
        Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');
    }

    /**
     * @expectedException \Braintree\Exception\Configuration
     * @expectedExceptionMessage merchantId needs to be set.
     */
    function testConfigGetsAssertedValid()
    {
        Configuration::environment('development');
        //Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');

        $gateway = new Gateway(Configuration::$global);
        $gateway->addOn();
    }

    function testConstructWithArrayOfCredentials()
    {
        $gateway = new Gateway(array(
            'environment' => 'sandbox',
            'merchantId'  => 'sandbox_merchant_id',
            'publicKey'   => 'sandbox_public_key',
            'privateKey'  => 'sandbox_private_key'
        ));

        $this->assertEquals('sandbox', $gateway->config->getEnvironment());
        $this->assertEquals('sandbox_merchant_id', $gateway->config->getMerchantId());
    }
}
