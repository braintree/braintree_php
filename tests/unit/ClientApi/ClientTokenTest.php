<?php

namespace Test\Unit\ClientApi;

require_once dirname(dirname(__DIR__)) . '/Setup.php';

use Test\Setup;
use Braintree;

class TestClientTokenGateway extends Braintree\ClientTokenGateway
{
    public $generatedParams;

    public function _doGenerate($subPath, $params)
    {
        $this->generatedParams = $params;
        return "client-token";
    }
}

class ClientTokenTest extends Setup
{
    public function testErrorsWhenCreditCardOptionsGivenWithoutCustomerId()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: options[makeDefault]');
        Braintree\ClientToken::generate(["options" => ["makeDefault" => true]]);
    }

    public function testErrorsWhenInvalidArgumentIsSupplied()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: customrId');
        Braintree\ClientToken::generate(["customrId" => "1234"]);
    }

    public function testRenamesPreferredPaymentMethodTokenToPaymentMethodIdWhenValueIsNull()
    {
        $gateway = new Braintree\Gateway(Braintree\Configuration::$global);
        $clientTokenGateway = new TestClientTokenGateway($gateway);

        $clientTokenGateway->generate([
            "customerId" => "a-customer-id",
            "preferredPaymentMethodToken" => null,
        ]);

        $clientTokenParams = $clientTokenGateway->generatedParams["client_token"];
        $this->assertArrayHasKey("paymentMethodId", $clientTokenParams);
        $this->assertNull($clientTokenParams["paymentMethodId"]);
        $this->assertArrayNotHasKey("preferredPaymentMethodToken", $clientTokenParams);
    }
}
