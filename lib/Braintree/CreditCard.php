<?php
/**
 * Braintree CreditCard module
 * Creates and manages Braintree CreditCards
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on CreditCards, see {@link http://www.braintreepayments.com/gateway/credit-card-api http://www.braintreepaymentsolutions.com/gateway/credit-card-api}<br />
 * For more detailed information on CreditCard verifications, see {@link http://www.braintreepayments.com/gateway/credit-card-verification-api http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api}
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $billingAddress
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read string $cardholderName
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
class Braintree_CreditCard extends Braintree_Base
{
    // Card Type
    const AMEX = 'American Express';
    const CARTE_BLANCHE = 'Carte Blanche';
    const CHINA_UNION_PAY = 'China UnionPay';
    const DINERS_CLUB_INTERNATIONAL = 'Diners Club';
    const DISCOVER = 'Discover';
    const JCB = 'JCB';
    const LASER = 'Laser';
    const MAESTRO = 'Maestro';
    const MASTER_CARD = 'MasterCard';
    const SOLO = 'Solo';
    const SWITCH_TYPE = 'Switch';
    const VISA = 'Visa';
    const UNKNOWN = 'Unknown';

    // Credit card origination location
	const INTERNATIONAL = "international";
	const US            = "us";

    const PREPAID_YES = 'Yes';
    const PREPAID_NO = 'No';
    const PREPAID_UNKNOWN = 'Unknown';

    const PAYROLL_YES = 'Yes';
    const PAYROLL_NO = 'No';
    const PAYROLL_UNKNOWN = 'Unknown';

    const HEALTHCARE_YES = 'Yes';
    const HEALTHCARE_NO = 'No';
    const HEALTHCARE_UNKNOWN = 'Unknown';

    const DURBIN_REGULATED_YES = 'Yes';
    const DURBIN_REGULATED_NO = 'No';
    const DURBIN_REGULATED_UNKNOWN = 'Unknown';

    const DEBIT_YES = 'Yes';
    const DEBIT_NO = 'No';
    const DEBIT_UNKNOWN = 'Unknown';

    const COMMERCIAL_YES = 'Yes';
    const COMMERCIAL_NO = 'No';
    const COMMERCIAL_UNKNOWN = 'Unknown';

    const COUNTRY_OF_ISSUANCE_UNKNOWN = "Unknown";
    const ISSUING_BANK_UNKNOWN = "Unknown";

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
     * checks whether the card is associated with venmo sdk
     *
     * @return boolean
     */
    public function isVenmoSdk()
    {
        return $this->venmoSdk;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $creditCardAttribs array of creditcard data
     * @return none
     */
    protected function _initialize($creditCardAttribs)
    {
        // set the attributes
        $this->_attributes = $creditCardAttribs;

        // map each address into its own object
        $billingAddress = isset($creditCardAttribs['billingAddress']) ?
            Braintree_Address::factory($creditCardAttribs['billingAddress']) :
            null;

        $subscriptionArray = array();
        if (isset($creditCardAttribs['subscriptions'])) {
            foreach ($creditCardAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = Braintree_Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
        $this->_set('billingAddress', $billingAddress);
        $this->_set('expirationDate', $this->expirationMonth . '/' . $this->expirationYear);
        $this->_set('maskedNumber', $this->bin . '******' . $this->last4);

        if(isset($creditCardAttribs['verifications']) && count($creditCardAttribs['verifications']) > 0) {
            $verifications = $creditCardAttribs['verifications'];
            usort($verifications, array($this, '_compareCreatedAtOnVerifications'));

            $this->_set('verification', Braintree_CreditCardVerification::factory($verifications[0]));
        }
    }

    private function _compareCreatedAtOnVerifications($verificationAttrib1, $verificationAttrib2)
    {
        return ($verificationAttrib2['createdAt'] < $verificationAttrib1['createdAt']) ? -1 : 1;
    }

    /**
     * returns false if comparing object is not a Braintree_CreditCard,
     * or is a Braintree_CreditCard with a different id
     *
     * @param object $otherCreditCard customer to compare against
     * @return boolean
     */
    public function isEqual($otherCreditCard)
    {
        return !($otherCreditCard instanceof Braintree_CreditCard) ? false : $this->token === $otherCreditCard->token;
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($this->_attributes) .']';
    }

    /**
     *  factory method: returns an instance of Braintree_CreditCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree_CreditCard
     */
    public static function factory($attributes)
    {
        $defaultAttributes = array(
            'bin' => '',
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => '',
        );

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }


    // static methods redirecting to gateway

    public static function create($attribs)
    {
        return Braintree_Configuration::gateway()->creditCard()->create($attribs);
    }

    public static function createNoValidate($attribs)
    {
        return Braintree_Configuration::gateway()->creditCard()->createNoValidate($attribs);
    }

    public static function createFromTransparentRedirect($queryString)
    {
        return Braintree_Configuration::gateway()->creditCard()->createFromTransparentRedirect($queryString);
    }

    public static function createCreditCardUrl()
    {
        return Braintree_Configuration::gateway()->creditCard()->createCreditCardUrl();
    }

    public static function expired()
    {
        return Braintree_Configuration::gateway()->creditCard()->expired();
    }

    public static function fetchExpired($ids)
    {
        return Braintree_Configuration::gateway()->creditCard()->fetchExpired($ids);
    }

    public static function expiringBetween($startDate, $endDate)
    {
        return Braintree_Configuration::gateway()->creditCard()->expiringBetween($startDate, $endDate);
    }

    public static function fetchExpiring($startDate, $endDate, $ids)
    {
        return Braintree_Configuration::gateway()->creditCard()->fetchExpiring($startDate, $endDate, $ids);
    }

    public static function find($token)
    {
        return Braintree_Configuration::gateway()->creditCard()->find($token);
    }

    public static function fromNonce($nonce)
    {
        return Braintree_Configuration::gateway()->creditCard()->fromNonce($nonce);
    }

    public static function credit($token, $transactionAttribs)
    {
        return Braintree_Configuration::gateway()->creditCard()->credit($token, $transactionAttribs);
    }

    public static function creditNoValidate($token, $transactionAttribs)
    {
        return Braintree_Configuration::gateway()->creditCard()->creditNoValidate($token, $transactionAttribs);
    }

    public static function sale($token, $transactionAttribs)
    {
        return Braintree_Configuration::gateway()->creditCard()->sale($token, $transactionAttribs);
    }

    public static function saleNoValidate($token, $transactionAttribs)
    {
        return Braintree_Configuration::gateway()->creditCard()->saleNoValidate($token, $transactionAttribs);
    }

    public static function update($token, $attributes)
    {
        return Braintree_Configuration::gateway()->creditCard()->update($token, $attributes);
    }

    public static function updateNoValidate($token, $attributes)
    {
        return Braintree_Configuration::gateway()->creditCard()->updateNoValidate($token, $attributes);
    }

    public static function updateCreditCardUrl()
    {
        return Braintree_Configuration::gateway()->creditCard()->updateCreditCardUrl();
    }

    public static function updateFromTransparentRedirect($queryString)
    {
        return Braintree_Configuration::gateway()->creditCard()->updateFromTransparentRedirect($queryString);
    }

    public static function delete($token)
    {
        return Braintree_Configuration::gateway()->creditCard()->delete($token);
    }

    public static function allCardTypes()
    {
        return array(
             Braintree_CreditCard::AMEX,
             Braintree_CreditCard::CARTE_BLANCHE,
             Braintree_CreditCard::CHINA_UNION_PAY,
             Braintree_CreditCard::DINERS_CLUB_INTERNATIONAL,
             Braintree_CreditCard::DISCOVER,
             Braintree_CreditCard::JCB,
             Braintree_CreditCard::LASER,
             Braintree_CreditCard::MAESTRO,
             Braintree_CreditCard::MASTER_CARD,
             Braintree_CreditCard::SOLO,
             Braintree_CreditCard::SWITCH_TYPE,
             Braintree_CreditCard::VISA,
             Braintree_CreditCard::UNKNOWN
        );
    }
}
