<?php
namespace Braintree;

/**
 * Braintree AndroidPayCard module
 * Creates and manages Braintree Android Pay cards
 *
 * <b>== More information ==</b>
 *
 * See {@link https://developers.braintreepayments.com/javascript+php}<br />
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read string $createdAt
 * @property-read string $default
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $googleTransactionId
 * @property-read string $imageUrl
 * @property-read string $last4
 * @property-read string $sourceCardLast4
 * @property-read string $sourceCardType
 * @property-read string $sourceDescription
 * @property-read string $token
 * @property-read string $updatedAt
 * @property-read string $virtualCardLast4
 * @property-read string $virtualCardType
 */
class AndroidPayCard extends Braintree
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
     *  factory method: returns an instance of Braintree\AndroidPayCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree\AndroidPayCard
     */
    public static function factory($attributes)
    {
        $defaultAttributes = array(
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => $attributes['virtualCardLast4'],
            'cardType'  => $attributes['virtualCardType'],
        );

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $androidPayCardAttribs array of Android Pay card properties
     * @return none
     */
    protected function _initialize($androidPayCardAttribs)
    {
        // set the attributes
        $this->_attributes = $androidPayCardAttribs;

        $subscriptionArray = array();
        if (isset($androidPayCardAttribs['subscriptions'])) {
            foreach ($androidPayCardAttribs['subscriptions'] as $subscription) {
                $subscriptionArray[] = Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
    }
}
