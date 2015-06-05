<?php namespace Braintree;

    /**
     * Braintree PaymentMethodNonceGateway module
     *
     * @package    Braintree
     * @category   Resources
     * @copyright  2014 Braintree, a division of PayPal, Inc.
     */
use Braintree\Result\Successful;
use Braintree\Exception\NotFound;

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
        $subPath = '/payment_methods/' . $token . '/nonces';
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath);

        return new Successful(
            PaymentMethodNonce::factory($response['paymentMethodNonce']),
            "paymentMethodNonce"
        );
    }

    /**
     * @access public
     *
     */
    public function find($nonce)
    {
        try {
            $path = $this->_config->merchantPath() . '/payment_method_nonces/' . $nonce;
            $response = $this->_http->get($path);
            return PaymentMethodNonce::factory($response['paymentMethodNonce']);
        } catch (NotFound $e) {
            throw new NotFound(
                'payment method nonce with id ' . $id . ' not found'
            );
        }

    }
}
