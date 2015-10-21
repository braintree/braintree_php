<?php
/**
 * Braintree AmexExpressCheckoutCard module
 * Creates and manages Braintree Amex Express Checkout cards
 *
 * <b>== More information ==</b>
 *
 * See {@link https://developers.braintreepayments.com/javascript+php}<br />
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $createdAt
 * @property-read string $default
 * @property-read string $updatedAt
 * @property-read string $customerId
 * @property-read string $cardType
 * @property-read string $bin
 * @property-read string $cardMemberExpiryDate
 * @property-read string $cardMemberNumber
 * @property-read string $cardType
 * @property-read string $sourceDescription
 * @property-read string $token
 * @property-read string $imageUrl
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 */
class Braintree_AmexExpressCheckoutCard extends Braintree_Base
{
    /* instance methods */
    /**
     * returns false if default is null or false
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     *  factory method: returns an instance of Braintree_AmexExpressCheckoutCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree_AmexExpressCheckoutCard
     */
    public static function factory($attributes)
    {

        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $amexExpressCheckoutCardAttribs array of Amex Express Checkout card properties
     * @return none
     */
    protected function _initialize($amexExpressCheckoutCardAttribs)
    {
        // set the attributes
        $this->_attributes = $amexExpressCheckoutCardAttribs;

        $subscriptionArray = array();
        if (isset($amexExpressCheckoutCardAttribs['subscriptions'])) {
            foreach ($amexExpressCheckoutCardAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = Braintree_Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
    }
}
