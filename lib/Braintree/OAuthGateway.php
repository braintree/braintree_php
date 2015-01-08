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
        $this->_http = new Braintree_Http($gateway->config);
    }

    public function createAccessToken($params)
    {
        $this->_config->assertHasClientCredentials();

        $response = $this->_http->post('/oauth/access_tokens', array('accessToken' => $params));
        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response['accessToken'])) {
            if (isset($response['accessToken']['error'])) {
                // TODO: Fix Error Handling
                // $errors = array('errors' => array($response['accessToken']));
                // return new Braintree_Result_Error($errors);
                $failure = new Braintree_Result_Successful(
                        Braintree_OAuthCredentials::factory($response['accessToken'])
                );
                $failure->success = false;
                return $failure;
            } else {
                return new Braintree_Result_Successful(
                        Braintree_OAuthCredentials::factory($response['accessToken'])
                );
            }
        } else {
            throw new Braintree_Exception_Unexpected(
                "Expected accessToken or apiErrorResponse"
            );
        }
    }

    public function connectUrl($params = array())
    {
        $this->_config->assertHasClientId();

        $query = array('client_id' => $this->_config->getClientId());
        if (isset($params['merchantId'])) {
            $query['merchant_id'] = $params['merchantId'];
        }
        if (isset($params['redirectUri'])) {
            $query['redirect_uri'] = $params['redirectUri'];
        }
        if (isset($params['scopes'])) {
            $query['scopes'] = $params['scopes'];
        }
        if (isset($params['state'])) {
            $query['state'] = $params['state'];
        }
        return $this->_config->baseUrl() . '/oauth/connect?' . http_build_query($query);
    }
}
