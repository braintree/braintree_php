<?php
namespace Braintree;

/**
 * Braintree GooglePayCard module
 * Creates and manages Braintree Google Pay cards
 *
 * <b>== More information ==</b>
 *
 * See {@link https://developers.braintreepayments.com/javascript+php}<br />
 *
 * @package    Braintree
 * @category   Resources
 *
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read \DateTime $createdAt
 * @property-read string $customerId
 * @property-read boolean $default
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $googleTransactionId
 * @property-read string $imageUrl
 * @property-read boolean $isNetworkTokenized
 * @property-read string $last4
 * @property-read string $sourceCardLast4
 * @property-read string $sourceCardType
 * @property-read string $sourceDescription
 * @property-read \Braintree\Subscription[] $subscriptions
 * @property-read string $token
 * @property-read \DateTime $updatedAt
 * @property-read string $virtualCardLast4
 * @property-read string $virtualCardType
 */
class GooglePayCard extends Base
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
     *  factory method: returns an instance of GooglePayCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return GooglePayCard
     */
    public static function factory($attributes)
    {
        $defaultAttributes = [
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => $attributes['virtualCardLast4'],
            'cardType'  => $attributes['virtualCardType'],
        ];

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $googlePayCardAttribs array of Google Pay card properties
     * @return void
     */
    protected function _initialize($googlePayCardAttribs)
    {
        // set the attributes
        $this->_attributes = $googlePayCardAttribs;

        $subscriptionArray = [];
        if (isset($googlePayCardAttribs['subscriptions'])) {
            foreach ($googlePayCardAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
    }
}
