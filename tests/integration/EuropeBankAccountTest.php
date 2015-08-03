<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_EuropeBankAccountTest extends PHPUnit_Framework_TestCase
{

    function testCanExchangeNonceForEuropeBankAccount()
    {
        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'altpay_merchant',
            'publicKey' => 'altpay_merchant_public_key',
            'privateKey' => 'altpay_merchant_private_key'
        ));

        $result = $gateway->customer()->create();
        $this->assertTrue($result->success);
        $customer = $result->customer;
        $clientApi = new Braintree_HttpClientApi($gateway->config);
        $nonce = $clientApi->nonceForNewEuropeanBankAccount(array(
            "customerId" => $customer->id,
            "sepa_mandate" => array(
                "locale" => "de-DE",
                "bic" => "DEUTDEFF",
                "iban" => "DE89370400440532013000",
                "accountHolderName" => "Bob Holder",
                "billingAddress" => array(
                    "streetAddress" => "123 Currywurst Way",
                    "extendedAddress" => "Lager Suite",
                    "firstName" => "Wilhelm",
                    "lastName" => "Dix",
                    "locality" => "Frankfurt",
                    "postalCode" => "60001",
                    "countryCodeAlpha2" => "DE",
                    "region" => "Hesse"
                )
            )
        ));
        $result = $gateway->paymentMethod()->create(array(
            "customerId" => $customer->id,
            "paymentMethodNonce" => $nonce
        ));

        $this->assertTrue($result->success);
        $paymentMethod = $result->paymentMethod;
        $account = $gateway->paymentMethod()->find($paymentMethod->token);
        $this->assertEquals($paymentMethod->token, $account->token);
        $this->assertEquals($account->bic, "DEUTDEFF");
    }
}
