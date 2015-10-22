<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class MerchantTest extends Setup
{
    public function testCreateMerchant()
    {
        $gateway = new Braintree\Gateway(array(
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ));
        $result = $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
            'paymentMethods' => ['credit_card', 'paypal'],
        ));

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);
    }

    /**
    * @expectedException Braintree\Exception\Configuration
    * @expectedExceptionMessage clientId needs to be passed to Braintree\Gateway
    */
    public function testAssertsHasCredentials()
    {
        $gateway = new Braintree\Gateway(array(
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ));
        $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
        ));
    }

    public function testBadPaymentMethods()
    {
        $gateway = new Braintree\Gateway(array(
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ));
        $result = $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
            'paymentMethods' => ['fake_money'],
        ));

        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('merchant')->onAttribute('paymentMethods');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_ACCOUNT_PAYMENT_METHODS_ARE_INVALID, $errors[0]->code);
    }
}
