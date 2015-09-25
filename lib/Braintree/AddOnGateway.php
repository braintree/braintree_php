<?php
namespace Braintree;

class AddOnGateway
{
    /**
     *
     * @var Braintree\Gateway
     */
    private $_gateway;

    /**
     *
     * @var Braintree\Configuration
     */
    private $_config;

    /**
     *
     * @var Braintree\Http
     */
    private $_http;

    /**
     *
     * @param Braintree\Gateway $gateway
     */
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }

    /**
     *
     * @return Braintree\AddOn[]
     */
    public function all()
    {
        $path = $this->_config->merchantPath().'/add_ons';
        $response = $this->_http->get($path);

        $addOns = array('addOn' => $response['addOns']);

        return Util::extractAttributeAsArray(
            $addOns,
            'addOn'
        );
    }
}
