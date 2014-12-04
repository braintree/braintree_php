<?php
class Braintree_AddOnGateway
{
    private $_gateway;
    private $_config;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
    }

    public function all()
    {
        $response = $this->_config->http()->get('/add_ons');

        $addOns = array("addOn" => $response['addOns']);

        return Braintree_Util::extractAttributeAsArray(
            $addOns,
            'addOn'
        );
    }
}
