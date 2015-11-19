<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class EuropeBankAccountTest extends Setup
{
    public function testCanExchangeNonceForEuropeBankAccount()
    {
        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'altpay_merchant',
            'publicKey' => 'altpay_merchant_public_key',
            'privateKey' => 'altpay_merchant_private_key'
        ]);

        $result = $gateway->customer()->create();
        $this->assertTrue($result->success);
        $customer = $result->customer;
        $clientApi = new HttpClientApi($gateway->config);
        $nonce = $clientApi->nonceForNewEuropeanBankAccount([
            "customerId" => $customer->id,
            "sepa_mandate" => [
                "locale" => "de-DE",
                "bic" => "DEUTDEFF",
                "iban" => "DE89370400440532013000",
                "accountHolderName" => "Bob Holder",
                "billingAddress" => [
                    "streetAddress" => "123 Currywurst Way",
                    "extendedAddress" => "Lager Suite",
                    "firstName" => "Wilhelm",
                    "lastName" => "Dix",
                    "locality" => "Frankfurt",
                    "postalCode" => "60001",
                    "countryCodeAlpha2" => "DE",
                    "region" => "Hesse"
                ]
            ]
        ]);
        $result = $gateway->paymentMethod()->create([
            "customerId" => $customer->id,
            "paymentMethodNonce" => $nonce
        ]);

        $this->assertTrue($result->success);
        $paymentMethod = $result->paymentMethod;
        $account = $gateway->paymentMethod()->find($paymentMethod->token);
        $this->assertEquals($paymentMethod->token, $account->token);
        $this->assertEquals($account->bic, "DEUTDEFF");
    }
}
