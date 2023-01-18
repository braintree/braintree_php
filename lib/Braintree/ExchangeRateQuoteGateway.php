<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuoteGateway module
 *
 * Manages exchange rate quote
 */
class ExchangeRateQuoteGateway
{
    private $_config;
    private $_graphQLClient;
    private $_http;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {

        $this->_config = $gateway->config;
        $this->_graphQLClient = new GraphQLClient($gateway->config);
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }

/**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function createSignature()
    {
        return[
                ['quotes' => ['baseCurrency' ,
                'quoteCurrency',
                'baseAmount',
                'markup'
                ],]
            ];
    }

    private function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        $request = ['input' => $attribs];
        return $request;
    }


    /**
     * Braintree ExchangeRateQuoteGateway module
     *
     * Manages exchange rate quote mutation for GraphQL API
     *
     * @param $input GenerateExchangeRateQuoteInput
     *
     * @return response
     */
    public function generate($input)
    {

        $definition = '
        mutation GenerateExchangeRateQuoteInput($input: GenerateExchangeRateQuoteInput!) {
            generateExchangeRateQuote(input: $input) {
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

        $request = $this->create($input);
        $response = $this->_graphQLClient->query($definition, $request);

        return $this->_verifyGatewayResponse($response);
        ;
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response["data"]["generateExchangeRateQuote"]["quotes"])) {
            // return a populated instance of ExchangeRateQuoteResponse
            return ExchangeRateQuoteResponse::factory($response["data"]["generateExchangeRateQuote"]);
        } elseif (isset($response['errors'])) {
            return $response['errors'][0]['message'];
        } else {
            throw new Exception\Unexpected(
                "Unexpected API Error"
            );
        }
    }
}
