<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class BraintreeGatewayTest extends Setup
{
    public function testGraphQLCanTokenizeCreditCard()
    {
        $this->markTestSkipped( 'Skipping until we have a more stable CI env' );
        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
        $definition = 'mutation ExampleServerSideSingleUseToken($input: TokenizeCreditCardInput!) {
  tokenizeCreditCard(input: $input) {
    paymentMethod {
      id
      usage
      details {
        ... on CreditCardDetails {
          bin
          brandCode
          last4
          expirationYear
          expirationMonth
        }
      }
    }
  }
}';
        $variables = [
          "input" => [
            "creditCard" => [
              "number" => "4005519200000004",
              "expirationYear" => "2024",
              "expirationMonth" => "05",
              "cardholderName" => "Joe Bloggs"
            ]
          ]
        ];
        $response = $gateway->graphQLClient->query($definition, $variables);

        $paymentMethod = $response["data"]["tokenizeCreditCard"]["paymentMethod"];
        $details = $paymentMethod["details"];

        $this->assertIsString($paymentMethod["id"]);
        $this->assertEquals("400551", $details["bin"]);
        $this->assertEquals("0004", $details["last4"]);
        $this->assertEquals("VISA", $details["brandCode"]);
        $this->assertEquals("05", $details["expirationMonth"]);
        $this->assertEquals("2024", $details["expirationYear"]);
    }

    public function testGraphQLRequestWithoutVariables()
    {
        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
        $definition = "query { ping }";
        $response = $gateway->graphQLClient->query($definition);

        $this->assertEquals('pong', $response["data"]["ping"]);
    }
}
