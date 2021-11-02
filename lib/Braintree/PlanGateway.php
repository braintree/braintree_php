<?php

namespace Braintree;

/**
 * Braintree PlanGateway module
 * Creates and manages Plans
 */
class PlanGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }

    /*
     * Retrieve all plans
     *
     * @return array of Plan objects
     */
    public function all()
    {
        $path = $this->_config->merchantPath() . '/plans';
        $response = $this->_http->get($path);
        if (key_exists('plans', $response)) {
            $plans = ["plan" => $response['plans']];
        } else {
            $plans = ["plan" => []];
        }

        return Util::extractAttributeAsArray(
            $plans,
            'plan'
        );
    }
}
