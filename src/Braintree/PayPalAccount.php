<?php
namespace Braintree;

/**
 * Braintree PayPalAccount module.
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree PayPalAccounts.
 *
 * <b>== More information ==</b>
 *
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $email
 * @property-read string $token
 * @property-read string $imageUrl
 */
class PayPalAccount extends Braintree
{
    /**
     *  factory method: returns an instance of PayPalAccount
     *  to the requesting method, with populated properties.
     *
     * @ignore
     *
     * @return object instance of PayPalAccount
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
     * @param array $paypalAccountAttribs array of paypalAccount data
     *
     * @return none
     */
    protected function _initialize($paypalAccountAttribs)
    {
        // set the attributes
        $this->_attributes = $paypalAccountAttribs;

        $subscriptionArray = array();
        if (isset($paypalAccountAttribs['subscriptions'])) {
            foreach ($paypalAccountAttribs['subscriptions'] as $subscription) {
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
        return Configuration::gateway()->payPalAccount()->find($token);
    }

    public static function update($token, $attributes)
    {
        return Configuration::gateway()->payPalAccount()->update($token, $attributes);
    }

    public static function delete($token)
    {
        return Configuration::gateway()->payPalAccount()->delete($token);
    }

    public static function sale($token, $transactionAttribs)
    {
        return Configuration::gateway()->payPalAccount()->sale($token, $transactionAttribs);
    }
}
