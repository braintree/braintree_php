<?php

namespace Braintree;

/**
 * Braintree PaymentMethodNonceGateway module.
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethodNonces.
 *
 * <b>== More information ==</b>
 *
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class PaymentMethodNonceGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Http($gateway->config);
    }

    public function create($token)
    {
        $subPath = '/payment_methods/'.$token.'/nonces';
        $fullPath = $this->_config->merchantPath().$subPath;
        $response = $this->_http->post($fullPath);

        return new Result\Successful(
            PaymentMethodNonce::factory($response['paymentMethodNonce']),
            'paymentMethodNonce'
        );
    }
}
