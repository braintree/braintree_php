<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class ApplePayTest extends setup
{
    private function _buildMerchantGateway()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);

        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        return new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);
    }

    public function testRegisterDomain()
    {
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registerDomain('domain');
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->registeredDomains();
        $this->assertEquals(true, $result->success);
        $registeredDomains = $result->applePayOptions->domains;
        $this->assertEmpty(array_diff(['domain'], $registeredDomains));
    }

    public function testValidationErrorWhenRegisteringNoDomain()
    {
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registerDomain('');
        $this->assertEquals(false, $result->success);
        $this->assertEquals(1, preg_match('/Domain name is required\./', $result->message));
    }

    public function testUnregisterDomain()
    {
        $domain = 'example.com';
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registerDomain($domain);
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->unregisterDomain($domain);
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->registeredDomains();
        $this->assertEmpty($result->applePayOptions->domains);
    }

    public function testUnregisterNonRegisteredDomain()
    {
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->unregisterDomain('http://non-registered-domain.com');
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->registeredDomains();
        $this->assertEmpty($result->applePayOptions->domains);
    }

    public function testUnregisterDomainWithSpecialCharacters()
    {
        $domain = 'ex&mple.com';
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registerDomain($domain);
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->unregisterDomain($domain);
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->registeredDomains();
        $this->assertEmpty($result->applePayOptions->domains);
    }

    public function testUnregisterDomainWithScheme()
    {
        $domain = 'http://example.com';
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registerDomain($domain);
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->unregisterDomain($domain);
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->registeredDomains();
        $this->assertEmpty($result->applePayOptions->domains);
    }

    public function testRegisteredDomains()
    {
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registerDomain('example.com');
        $this->assertEquals(true, $result->success);
        $result = $gateway->applePay()->registerDomain('example.org');
        $this->assertEquals(true, $result->success);

        $result = $gateway->applePay()->registeredDomains();
        $this->assertEquals(true, $result->success);
        $registeredDomains = $result->applePayOptions->domains;
        $this->assertEmpty(array_diff(['example.com', 'example.org'], $registeredDomains));
    }

    public function testNoRegisteredDomains()
    {
        $gateway = $this->_buildMerchantGateway();
        $result = $gateway->applePay()->registeredDomains();
        $this->assertEquals(true, $result->success);
        $this->assertEmpty($result->applePayOptions->domains);
    }
}
