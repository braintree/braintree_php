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
    private $_config;
    private $_http;

    public function __construct($config)
    {
        $this->config = $config;
        if (is_array($config)) {
            $config = new Braintree_Configuration($config);
        }
        $config->assertValidForOAuth();
        $this->_http = new Braintree_Http($config);
    }

    public function createAccessToken($params)
    {
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
                        Braintree_AccessToken::factory($response['accessToken'])
                );
                $failure->success = false;
                return $failure;
            } else {
                return new Braintree_Result_Successful(
                        Braintree_AccessToken::factory($response['accessToken'])
                );
            }
        } else {
            throw new Braintree_Exception_Unexpected(
                "Expected accessToken or apiErrorResponse"
            );
        }
    }
}
