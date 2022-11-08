<?php

namespace Braintree;

/**
 * Braintree SepaDirectDebitAccount module
 * Manages Braintree SepaDirectDebitAccounts
 *
 * See our {@link https://developer.paypal.com/braintree/docs/reference/response/sepa-direct-debit-account/php developer docs} for information on attributes
 */
class SepaDirectDebitAccount extends Base
{
    /**
     * Creates an instance from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return SepaDirectDebitAccount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /* instance methods */

    /**
     * Returns false if default is null or false
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Sets instance properties from an array of values
     *
     * @param array $sepaDirectDebitAttribs array of sepaDirectDebitAccount data
     *
     * @return void
     */
    protected function _initialize($sepaDirectDebitAttribs)
    {
        $this->_attributes = $sepaDirectDebitAttribs;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }

    /**
     * Static methods redirecting to gateway class
     *
     * @param string $token paypal account unique id
     *
     * @see SepaDirectDebitAccountGateway::find()
     *
     * @throws Exception\NotFound
     *
     * @return SepaDirectDebitAccount
     */
    public static function find($token)
    {
        return Configuration::gateway()->sepaDirectDebitAccount()->find($token);
    }

    /**
     * Static methods redirecting to gateway class
     *
     * @param string $token paypal account identifier
     *
     * @see PayPalGateway::delete()
     *
     * @return Result
     */
    public static function delete($token)
    {
        return Configuration::gateway()->sepaDirectDebitAccount()->delete($token);
    }

    /**
     * Static methods redirecting to gateway class
     *
     * @param string $token              paypal account identifier
     * @param array  $transactionAttribs containing request parameters
     *
     * @see PayPalGateway::sale()
     *
     * @return Result\Successful|Result\Error
     */
    public static function sale($token, $transactionAttribs)
    {
        return Configuration::gateway()->sepaDirectDebitAccount()->sale($token, $transactionAttribs);
    }
}
