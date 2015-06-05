<?php namespace Braintree;

final class MerchantGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasClientCredentials();
        $this->_http = new Http($gateway->config);
    }

    public function create($attribs)
    {
        $response = $this->_http->post('/merchants/create_via_api', array('merchant' => $attribs));
        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response['response']['merchant'])) {
            // return a populated instance of merchant
            return new Result_Successful(array(
                Merchant::factory($response['response']['merchant']),
                OAuthCredentials::factory($response['response']['credentials']),
            ));
        } else {
            if (isset($response['apiErrorResponse'])) {
                return new Result_Error($response['apiErrorResponse']);
            } else {
                throw new Exception_Unexpected(
                    "Expected merchant or apiErrorResponse"
                );
            }
        }
    }
}
