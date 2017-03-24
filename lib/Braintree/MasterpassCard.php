<?php
namespace Braintree;

/**
 * Braintree MasterpassCard module
 * Creates and manages Braintree MasterpassCards
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on CreditCard verifications, see {@link http://www.braintreepayments.com/gateway/credit-card-verification-api http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api}
 *
 * @package    Braintree
 * @category   Resources
 *
 * @property-read string $billingAddress
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read string $cardholderName
 * @property-read string $callId
 * @property-read string $createdAt
 * @property-read string $customerId
 * @property-read string $expirationDate
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $imageUrl
 * @property-read string $last4
 * @property-read string $maskedNumber
 * @property-read string $token
 * @property-read string $updatedAt
 */
class MasterpassCard extends Base
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
     * checks whether the card is expired based on the current date
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $creditCardAttribs array of creditcard data
     * @return void
     */
    protected function _initialize($creditCardAttribs)
    {
        // set the attributes
        $this->_attributes = $creditCardAttribs;

        // map each address into its own object
        $billingAddress = isset($creditCardAttribs['billingAddress']) ?
            Address::factory($creditCardAttribs['billingAddress']) :
            null;

        $subscriptionArray = [];
        if (isset($creditCardAttribs['subscriptions'])) {
            foreach ($creditCardAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
        $this->_set('billingAddress', $billingAddress);
        $this->_set('expirationDate', $this->expirationMonth . '/' . $this->expirationYear);
        $this->_set('maskedNumber', $this->bin . '******' . $this->last4);
    }

    /**
     * returns false if comparing object is not a CreditCard,
     * or is a CreditCard with a different id
     *
     * @param object $otherCreditCard customer to compare against
     * @return boolean
     */
    public function isEqual($otherMasterpassCard)
    {
        return !($otherMasterpassCard instanceof self) ? false : $this->token === $otherMasterpassCard->token;
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) .']';
    }

    /**
     *  factory method: returns an instance of CreditCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return MasterpassCard
     */
    public static function factory($attributes)
    {
        $defaultAttributes = [
            'bin' => '',
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => '',
        ];

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }


    // static methods redirecting to gateway

    public static function create($attribs)
    {
        return Configuration::gateway()->creditCard()->create($attribs);
    }

    public static function createNoValidate($attribs)
    {
        return Configuration::gateway()->creditCard()->createNoValidate($attribs);
    }

    public static function createFromTransparentRedirect($queryString)
    {
        return Configuration::gateway()->creditCard()->createFromTransparentRedirect($queryString);
    }

    public static function createCreditCardUrl()
    {
        return Configuration::gateway()->creditCard()->createCreditCardUrl();
    }

    public static function expired()
    {
        return Configuration::gateway()->creditCard()->expired();
    }

    public static function fetchExpired($ids)
    {
        return Configuration::gateway()->creditCard()->fetchExpired($ids);
    }

    public static function expiringBetween($startDate, $endDate)
    {
        return Configuration::gateway()->creditCard()->expiringBetween($startDate, $endDate);
    }

    public static function fetchExpiring($startDate, $endDate, $ids)
    {
        return Configuration::gateway()->creditCard()->fetchExpiring($startDate, $endDate, $ids);
    }

    public static function find($token)
    {
        return Configuration::gateway()->creditCard()->find($token);
    }

    public static function fromNonce($nonce)
    {
        return Configuration::gateway()->creditCard()->fromNonce($nonce);
    }

    public static function credit($token, $transactionAttribs)
    {
        return Configuration::gateway()->creditCard()->credit($token, $transactionAttribs);
    }

    public static function creditNoValidate($token, $transactionAttribs)
    {
        return Configuration::gateway()->creditCard()->creditNoValidate($token, $transactionAttribs);
    }

    public static function sale($token, $transactionAttribs)
    {
        return Configuration::gateway()->creditCard()->sale($token, $transactionAttribs);
    }

    public static function saleNoValidate($token, $transactionAttribs)
    {
        return Configuration::gateway()->creditCard()->saleNoValidate($token, $transactionAttribs);
    }

    public static function update($token, $attributes)
    {
        return Configuration::gateway()->creditCard()->update($token, $attributes);
    }

    public static function updateNoValidate($token, $attributes)
    {
        return Configuration::gateway()->creditCard()->updateNoValidate($token, $attributes);
    }

    public static function updateCreditCardUrl()
    {
        return Configuration::gateway()->creditCard()->updateCreditCardUrl();
    }

    public static function updateFromTransparentRedirect($queryString)
    {
        return Configuration::gateway()->creditCard()->updateFromTransparentRedirect($queryString);
    }

    public static function delete($token)
    {
        return Configuration::gateway()->creditCard()->delete($token);
    }

    /** @return array */
    public static function allCardTypes()
    {
        return [
            CreditCard::AMEX,
            CreditCard::CARTE_BLANCHE,
            CreditCard::CHINA_UNION_PAY,
            CreditCard::DINERS_CLUB_INTERNATIONAL,
            CreditCard::DISCOVER,
            CreditCard::JCB,
            CreditCard::LASER,
            CreditCard::MAESTRO,
            CreditCard::MASTER_CARD,
            CreditCard::SOLO,
            CreditCard::SWITCH_TYPE,
            CreditCard::VISA,
            CreditCard::UNKNOWN
        ];
    }
}
class_alias('Braintree\MasterpassCard', 'Braintree_MasterpassCard');
