<?php
/**
 * Braintree PaymentMethodNonceGateway module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethodNonces
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class Braintree_PaymentMethodNonceGateway
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


    public function create($token)
    {
        $subPath = '/payment_methods/' . $token . '/nonces';
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath);

        return new Braintree_Result_Successful(
            Braintree_PaymentMethodNonce::factory($response['paymentMethodNonce']),
            "paymentMethodNonce"
        );
    }
}
