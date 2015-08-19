<?php
final class Braintree_Test_Transaction
{
    /**
     * settle a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @param Braintree_Configuration $config gateway config
     * @return object Braintree_Transaction
     */
    public static function settle($transactionId)
    {
        return Braintree_Configuration::gateway()->testing()->settle($transactionId);
    }

    /**
     * settlement confirm a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @param Braintree_Configuration $config gateway config
     * @return object Braintree_Transaction
     */
    public static function settlementConfirm($transactionId)
    {
        return Braintree_Configuration::gateway()->testing()->settlementConfirm($transactionId);
    }

    /**
     * settlement decline a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @param Braintree_Configuration $config gateway config
     * @return object Braintree_Transaction
     */
    public static function settlementDecline($transactionId)
    {
        return Braintree_Configuration::gateway()->testing()->settlementDecline($transactionId);
    }

    /**
     * settlement pending a transaction by id in sandbox
     *
     * @param string $id transaction id
     * @param Braintree_Configuration $config gateway config
     * @return object Braintree_Transaction
     */
    public static function settlementPending($transactionId)
    {
        return Braintree_Configuration::gateway()->testing()->settlementPending($transactionId);
    }
}
