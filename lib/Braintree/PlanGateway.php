<?php
class Braintree_PlanGateway
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
        $response = $this->_config->http()->get('/plans');
        if (key_exists('plans', $response)){
            $plans = array("plan" => $response['plans']);
        } else {
            $plans = array("plan" => array());
        }

        return Braintree_Util::extractAttributeAsArray(
            $plans,
            'plan'
        );
    }
}
