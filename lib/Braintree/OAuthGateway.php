<?php
/**
 * Braintree OAuthGateway module
 * PHP Version 5
 * Creates and manages Braintree Addresses
 *
 * @package   Braintree
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_OAuthGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Braintree_HttpOAuth($gateway->config);
    }

    public function createTokenFromCode($params)
    {
        $this->_config->assertHasClientCredentials();

        $params['grantType'] = "authorization_code";
        $response = $this->_http->post('/oauth/access_tokens', $params);
        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        $result = Braintree_OAuthCredentials::factory($response);
        $result->success = !isset($response['error']);
        return $result;
    }

    public function connectUrl($params = array())
    {
        $this->_config->assertHasClientId();

        $query = Braintree_Util::camelCaseToDelimiterArray($params, '_');
        $query['client_id'] = $this->_config->getClientId();
        return $this->_config->baseUrl() . '/oauth/connect?' . http_build_query($query);
    }
}
