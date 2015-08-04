<?php
final class Braintree_Test_Transaction
{
    /**
     * settle a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settle($transactionId, $config)
    {
        return self::_doTestRequest('/settle', $transactionId, $config);
    }
    /**
     * settlement confirm a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settlementConfirm($transactionId, $config)
    {
        return self::_doTestRequest('/settlement_confirm', $transactionId, $config);
    }
    /**
     * settlement decline a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settlementDecline($transactionId, $config)
    {
        return self::_doTestRequest('/settlement_decline', $transactionId, $config);
    }
    /**
     * settlement pending a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settlementPending($transactionId, $config)
    {
        return self::_doTestRequest('/settlement_pending', $transactionId, $config);
    }
    private static function _doTestRequest($testPath, $transactionId, $config)
    {
        self::_checkEnvironment();
        $http = new Braintree_Http($config);
        $path = $config->merchantPath() . '/transactions/' . $transactionId . $testPath;
        $response = $http->put($path);
        return Braintree_Transaction::factory($response['transaction']);
    }
    private static function _checkEnvironment()
    {
        if (Braintree_Configuration::$global->getEnvironment() == 'production')
            throw new Braintree_Exception_TestOperationPerformedInProduction();
    }
}
