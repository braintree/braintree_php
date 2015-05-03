<?php
namespace Braintree;

/**
 * Braintree CoinbaseAccount module.
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree CoinbaseAccounts.
 *
 * <b>== More information ==</b>
 *
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $userId
 * @property-read string $userName
 * @property-read string $userEmail
 */
class CoinbaseAccount extends Braintree
{
    /**
     *  factory method: returns an instance of CoinbaseAccount
     *  to the requesting method, with populated properties.
     *
     * @ignore
     *
     * @return object instance of CoinbaseAccount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    /* instance methods */

    /**
     * returns false if default is null or false.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * sets instance properties from an array of values.
     *
     * @param array $coinbaseAccountAttribs array of coinbaseAccount data
     *
     * @return none
     */
    protected function _initialize($coinbaseAccountAttribs)
    {
        // set the attributes
        $this->_attributes = $coinbaseAccountAttribs;

        $subscriptionArray = array();
        if (isset($coinbaseAccountAttribs['subscriptions'])) {
            foreach ($coinbaseAccountAttribs['subscriptions'] as $subscription) {
                $subscriptionArray[] = Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value].
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__.'['.
                Util::attributesToString($this->_attributes).']';
    }

    // static methods redirecting to gateway

    public static function find($token)
    {
        return Configuration::gateway()->coinbaseAccount()->find($token);
    }

    public static function update($token, $attributes)
    {
        return Configuration::gateway()->coinbaseAccount()->update($token, $attributes);
    }

    public static function delete($token)
    {
        return Configuration::gateway()->coinbaseAccount()->delete($token);
    }

    public static function sale($token, $transactionAttribs)
    {
        return Configuration::gateway()->coinbaseAccount()->sale($token, $transactionAttribs);
    }
}
