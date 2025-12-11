<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

/**
 * @skip
 */
class MerchantTest extends Setup
{
    public function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('Merchant tests are pended');
    }
    public function testCreateMerchant()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);
    }

    public function testAssertsHasCredentials()
    {
        $gateway = new Braintree\Gateway([
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);

        $this->expectException('Braintree\Exception\Configuration', 'clientId needs to be passed to Braintree\Gateway');
        $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
        ]);
    }

    public function testBadPaymentMethods()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['fake_money'],
        ]);

        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('merchant')->onAttribute('paymentMethods');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_ACCOUNT_PAYMENT_METHODS_ARE_INVALID, $errors[0]->code);
    }

    public function testCreateEUMerchantThatAcceptsMultipleCurrencies()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
            'currencies' => ['GBP', 'USD']
        ]);

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);

        $merchantAccounts = $merchant->merchantAccounts;
        $this->assertEquals(2, count($merchantAccounts));

        $usdMerchantAccount = $this->getMerchantAccountForCurrency($merchantAccounts, 'USD');
        $this->assertNotNull($usdMerchantAccount);
        $this->assertEquals(false, $usdMerchantAccount->default);
        $this->assertEquals('USD', $usdMerchantAccount->currencyIsoCode);

        $gbpMerchantAccount = $this->getMerchantAccountForCurrency($merchantAccounts, 'GBP');
        $this->assertNotNull($gbpMerchantAccount);
        $this->assertEquals(true, $gbpMerchantAccount->default);
        $this->assertEquals('GBP', $gbpMerchantAccount->currencyIsoCode);
    }

    public function testCreatePaypalOnlyMerchantThatAcceptsMultipleCurrencies()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['paypal'],
            'currencies' => ['GBP', 'USD'],
            'paypalAccount' => [
                'clientId' => 'fake_client_id',
                'clientSecret' => 'fake_client_secret',
            ]
        ]);

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);

        $merchantAccounts = $merchant->merchantAccounts;
        $this->assertEquals(2, count($merchantAccounts));

        $usdMerchantAccount = $this->getMerchantAccountForCurrency($merchantAccounts, 'USD');
        $this->assertNotNull($usdMerchantAccount);
        $this->assertEquals(false, $usdMerchantAccount->default);
        $this->assertEquals('USD', $usdMerchantAccount->currencyIsoCode);

        $gbpMerchantAccount = $this->getMerchantAccountForCurrency($merchantAccounts, 'GBP');
        $this->assertNotNull($gbpMerchantAccount);
        $this->assertEquals(true, $gbpMerchantAccount->default);
        $this->assertEquals('GBP', $gbpMerchantAccount->currencyIsoCode);
    }

    private function getMerchantAccountForCurrency($merchantAccounts, $currency)
    {
        foreach ($merchantAccounts as $merchantAccount) {
            if ($merchantAccount->id == $currency) {
                return $merchantAccount;
            }
        }
        return null;
    }

    public function testCreatePaypalOnlyMerchantWithNoCurrenciesProvided()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['paypal'],
            'paypalAccount' => [
                'clientId' => 'fake_client_id',
                'clientSecret' => 'fake_client_secret',
            ]
        ]);

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);

        $merchantAccounts = $merchant->merchantAccounts;
        $this->assertEquals(1, count($merchantAccounts));

        $jpyMerchantAccount = $merchantAccounts[0];
        $this->assertEquals(true, $jpyMerchantAccount->default);
        $this->assertEquals('GBP', $jpyMerchantAccount->currencyIsoCode);
    }

    public function testInvalidCurrencyForMultiCurrency()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['paypal'],
            'currencies' => ['FAKE', 'USD'],
            'paypalAccount' => [
                'clientId' => 'fake_client_id',
                'clientSecret' => 'fake_client_secret',
            ]
        ]);

        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('merchant')->onAttribute('currencies');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_CURRENCIES_ARE_INVALID, $errors[0]->code);
    }
}
