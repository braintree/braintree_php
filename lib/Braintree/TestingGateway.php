<?php
final class Braintree_TestingGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Braintree_Http($this->_config);
    }

    public function settle($transactionId)
    {
        return self::_doTestRequest('/settle', $transactionId);
    }

    public function settlementPending($transactionId)
    {
        return self::_doTestRequest('/settlement_pending', $transactionId);
    }

    public function settlementConfirm($transactionId)
    {
        return self::_doTestRequest('/settlement_confirm', $transactionId);
    }

    public function settlementDecline($transactionId)
    {
        return self::_doTestRequest('/settlement_decline', $transactionId);
    }

    private function _doTestRequest($testPath, $transactionId)
    {
        self::_checkEnvironment();
        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . $testPath;
        $response = $this->_http->put($path);
        return Braintree_Transaction::factory($response['transaction']);
    }

    private function _checkEnvironment()
    {
        if (Braintree_Configuration::$global->getEnvironment() == 'production') {
            throw new Braintree_Exception_TestOperationPerformedInProduction();
        }
    }
}
