<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class MerchantAccountTest extends Setup
{
    private static $validParams = [
      'individual' => [
        'firstName' => "Joe",
        'lastName' => "Bloggs",
        'email' => "joe@bloggs.com",
        'phone' => "555-555-5555",
        'address' => [
          'streetAddress' => "123 Credibility St.",
          'postalCode' => "60606",
          'locality' => "Chicago",
          'region' => "IL",
        ],
        'dateOfBirth' => "10/9/1980",
        'ssn' => "123-00-1234",
      ],
      'business' => [
        'dbaName' => "Robot City",
        'legalName' => "Robot City INC",
        'taxId' => "123456789",
      ],
      'funding' => [
        'routingNumber' => "122100024",
        'accountNumber' => "43759348798",
        'destination' => Braintree\MerchantAccount::FUNDING_DESTINATION_BANK,
        'descriptor' => 'Joes Bloggs MI',
      ],
      'tosAccepted' => true,
      'masterMerchantAccountId' => "sandbox_master_merchant_account"
    ];

    public function testRetrievesMasterMerchantAccountCurrencyIsoCode()
    {
        $merchantAccount = Braintree\MerchantAccount::find("sandbox_master_merchant_account");

        $this->assertEquals("USD", $merchantAccount->currencyIsoCode);
    }

    public function testFind_throwsIfNotFound()
    {
        $this->expectException('Braintree\Exception\NotFound', 'merchant account with id does-not-exist not found');
        Braintree\MerchantAccount::find('does-not-exist');
    }

    public function testCreateForCurrency()
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

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "USD",
        ]);

        $this->assertEquals(true, $result->success);

        $merchantAccount = $result->merchantAccount;
        $this->assertEquals("USD", $merchantAccount->currencyIsoCode);
        $this->assertEquals("USD", $merchantAccount->id);
    }

    public function testCreateForCurrencyWithDuplicateCurrency()
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

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $merchantAccount = $result->merchant->merchantAccounts[0];
        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "GBP",
        ]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('currency');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_MERCHANT_ACCOUNT_EXISTS_FOR_CURRENCY, $errors[0]->code);
    }

    public function testCreateForCurrencyWithInvalidCurrency()
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

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "FAKE_CURRENCY",
        ]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('currency');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_CURRENCY_IS_INVALID, $errors[0]->code);
    }

    public function testCreateForCurrencyWithoutCurrency()
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

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->createForCurrency([]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('currency');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_CURRENCY_IS_REQUIRED, $errors[0]->code);
    }

    public function testCreateForCurrencyWithDuplicateId()
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

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $merchantAccount = $result->merchant->merchantAccounts[0];
        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "USD",
            'id' => $merchantAccount->id,
        ]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('id');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_MERCHANT_ACCOUNT_EXISTS_FOR_ID, $errors[0]->code);
    }

    public function testAllReturnsAllMerchantAccounts()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($gateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'read_write'
        ]);

        $credentials = $gateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $gateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $result = $gateway->merchantAccount()->all();
        $merchantAccounts = [];
        foreach ($result as $ma) {
            array_push($merchantAccounts, $ma);
        }
        $this->assertEquals(true, count($merchantAccounts) > 20);
    }

    public function testAllReturnsMerchantAccountWithCorrectAttributes()
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

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->all();
        $merchantAccounts = [];
        foreach ($result as $ma) {
            array_push($merchantAccounts, $ma);
        }

        $this->assertEquals(1, count($merchantAccounts));
        $merchantAccount = $merchantAccounts[0];
        $this->assertEquals("GBP", $merchantAccount->currencyIsoCode);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_ACTIVE, $merchantAccount->status);
        $this->assertTrue($merchantAccount->default);
    }

    public function testFind()
    {
        // Use an existing sub-merchant account ID for testing
        $merchantAccountId = "sandbox_sub_merchant_account";

        $merchantAccount = Braintree\MerchantAccount::find($merchantAccountId);

        // Assertions based on the known state of sandbox_sub_merchant_account
        $this->assertInstanceOf(Braintree\MerchantAccount::class, $merchantAccount);
        $this->assertEquals("active", $merchantAccount->status);
    }
}
