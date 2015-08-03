<?php
final class Braintree_Test_Transaction
{
    /**
     * settle a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settle($transactionId)
    {
        return self::_doTestRequest('/settle', $transactionId);
    }
    /**
     * settlement confirm a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settlementConfirm($transactionId)
    {
        return self::_doTestRequest('/settlement_confirm', $transactionId);
    }
    /**
     * settlement decline a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settlementDecline($transactionId)
    {
        return self::_doTestRequest('/settlement_decline', $transactionId);
    }
    /**
     * settlement pending a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @return object Braintree_Transaction
     */
    public static function settlementPending($transactionId)
    {
        return self::_doTestRequest('/settlement_pending', $transactionId);
    }
    private static function _doTestRequest($testPath, $transactionId)
    {
        self::_checkEnvironment();
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $transactionId . $testPath;
        $response = $http->put($path);
        return Braintree_Transaction::factory($response['transaction']);
    }
    private static function _checkEnvironment()
    {
        if (Braintree_Configuration::$global->getEnvironment() == 'production')
            throw new Braintree_Exception_TestOperationPerformedInProduction();
    }
}
