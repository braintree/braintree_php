<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class GraphQLTest extends Setup
{
    public function testGraphQLPing()
    {
        Braintree\Configuration::environment('development');
        $graphQL = new Braintree\GraphQL(Braintree\Configuration::$global);
        $definition = "query { ping }";
        $response = $graphQL->request($definition, null);

        $this->assertEquals('pong', $response["data"]["ping"]);
    }

    public function testGraphQLProductionSSL()
    {
        Braintree\Configuration::environment('production');
        $graphQL = new Braintree\GraphQL(Braintree\Configuration::$global);
        $definition = "query { ping }";

        $this->expectException('Braintree\Exception\Authentication');
        $response = $graphQL->request($definition, null);
    }

    public function testGraphQLSandboxSSL()
    {
        Braintree\Configuration::environment('sandbox');
        $graphQL = new Braintree\GraphQL(Braintree\Configuration::$global);
        $definition = "query { ping }";

        $this->expectException('Braintree\Exception\Authentication');
        $response = $graphQL->request($definition, null);
    }

    public function testexchangeRateQuoteMutation()
    {
        Braintree\Configuration::environment('development');
        
        $graphQL = new Braintree\GraphQL(Braintree\Configuration::$global);
        $definition = '
        mutation GenerateExchangeRateQuoteInput($input: GenerateExchangeRateQuoteInput!) {
            generateExchangeRateQuote(input: $input) {
                clientMutationId
                quotes{ 
                    id
                    baseAmount {value, currencyCode}
                    quoteAmount {value, currencyCode}
                    exchangeRate
                    tradeRate
                    expiresAt
                    refreshesAt
                }
            }
        }';
        $variables = [
            "input" => [
              "clientMutationId" => "abc123",
              "quotes"=> [
                "baseCurrency"=>"USD",
                "quoteCurrency"=> "EUR",
                "baseAmount"=> "12.19",
                 "markup"=> "1.89"
                    
              ]
            ]
          ];
        echo "Mutation String : $definition";
        echo "variables String : $variables";
        $response = $graphQL->request($definition, $variables);
        return $response;

        
    }

    
}
