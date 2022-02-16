<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuoteGateway module
 *
 * Manages exchange rate quote 
 */
class ExchangeRateQuoteGateway
{
    private $_gateway;
    private $_config;
    private $_http;
    private $_graphql;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
        $this->_graphql = $gateway->graphQLClient;

    }

    public function exchangeRateQuoteMutation($input){
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
        echo "Mutation String : $definition";
        $response = $graphQL->request($definition, $variables);
        return $response;
    }



}
