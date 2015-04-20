<?php

namespace Braintree;

class AddOnGateway
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
