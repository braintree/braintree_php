<?php
namespace Test\Integration;

require_once dirname(__DIR__).'/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class MerchantTest extends Setup
{
    function testCreateMerchant()
    {
        $gateway = new Braintree\Gateway(array(
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));
        $result = $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
        ));

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);
    }

    /**
    * @expectedException Exception\Configuration
    * @expectedExceptionMessage clientId needs to be set.
    */
    function testAssertsHasCredentials()
    {
        $gateway = new Braintree\Gateway(array(
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));
        $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
        ));
    }
}