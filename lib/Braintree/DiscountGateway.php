<?php
class Braintree_DiscountGateway
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
        $response = $this->_config->http()->get('/discounts');

        $discounts = array("discount" => $response['discounts']);

        return Braintree_Util::extractAttributeAsArray(
            $discounts,
            'discount'
        );
    }
}
