<?php

namespace Braintree;

/**
 * Braintree CreditCard module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates and manages Braintree CreditCards
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on CreditCards, see {@link http://www.braintreepayments.com/gateway/credit-card-api http://www.braintreepaymentsolutions.com/gateway/credit-card-api}<br />
 * For more detailed information on CreditCard verifications, see {@link http://www.braintreepayments.com/gateway/credit-card-verification-api http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api}
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
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
class CreditCard extends Braintree
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

    protected $default;
    protected $expired;
    protected $venmoSdk;

    public static function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        return self::_doCreate('/payment_methods', array('credit_card' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a CreditCard object instead of a Result
     *
     * @access public
     * @param array $attribs
     * @return object
     * @throws Exception\ValidationsFailed
     */
    public static function createNoValidate($attribs)
    {
        $result = self::create($attribs);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * create a customer from a TransparentRedirect operation
     *
     * @access public
     * @param $queryString
     * @return object
     */
    public static function createFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );
        return self::_doCreate(
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public static function createCreditCardUrl()
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::url", E_USER_NOTICE);
        return Configuration::merchantUrl() .
                '/payment_methods/all/create_via_transparent_redirect_request';
    }

    /**
     * returns a ResourceCollection of expired credit cards
     * @return object ResourceCollection
     */
    public static function expired()
    {
        $response = Http::post("/payment_methods/all/expired_ids");
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetchExpired',
            'methodArgs' => array()
        );

        return new ResourceCollection($response, $pager);
    }

    public static function fetchExpired($ids)
    {
        $response = Http::post("/payment_methods/all/expired", array('search' => array('ids' => $ids)));

        return Util::extractattributeasarray(
            $response['paymentMethods'],
            'creditCard'
        );
    }

    /**
     * returns a ResourceCollection of credit cards expiring between start/end
     *
     * @param $startDate
     * @param $endDate
     * @return object ResourceCollection
     */
    public static function expiringBetween($startDate, $endDate)
    {
        $queryPath = '/payment_methods/all/expiring_ids?start=' . date('mY', $startDate) . '&end=' . date('mY', $endDate);
        $response = Http::post($queryPath);
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetchExpiring',
            'methodArgs' => array($startDate, $endDate)
        );

        return new ResourceCollection($response, $pager);
    }

    public static function fetchExpiring($startDate, $endDate, $ids)
    {
        $queryPath = '/payment_methods/all/expiring?start=' . date('mY', $startDate) . '&end=' . date('mY', $endDate);
        $response = Http::post($queryPath, array('search' => array('ids' => $ids)));

        return Util::extractAttributeAsArray(
            $response['paymentMethods'],
            'creditCard'
        );
    }

    /**
     * find a creditcard by token
     *
     * @access public
     * @param string $token credit card unique id
     * @return object CreditCard
     * @throws Exception\NotFound
     */
    public static function find($token)
    {
        self::_validateId($token);
        try {
            $response = Http::get('/payment_methods/'.$token);
            return self::factory($response['creditCard']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'credit card with token ' . $token . ' not found'
            );
        }

    }

   /**
     * create a credit on the card for the passed transaction
     *
     * @access public
     * @param $token
     * @param $transactionAttribs
     * @return object Result\Successful or Result\Error
     */
    public static function credit($token, $transactionAttribs)
    {
        self::_validateId($token);
        return Transaction::credit(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a credit on this card, assuming validations will pass
     *
     * returns a Transaction object on success
     *
     * @access public
     * @param $token
     * @param $transactionAttribs
     * @return object Transaction
     */
    public static function creditNoValidate($token, $transactionAttribs)
    {
        $result = self::credit($token, $transactionAttribs);
        return self::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * create a new sale for the current card
     *
     * @param string $token
     * @param array $transactionAttribs
     * @return object Result\Successful or Result\Error
     * @see Transaction::sale()
     */
    public static function sale($token, $transactionAttribs)
    {
        self::_validateId($token);
        return Transaction::sale(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a new sale using this card, assuming validations will pass
     *
     * returns a Transaction object on success
     *
     * @access public
     * @param array $transactionAttribs
     * @param string $token
     * @return object Transaction
     * @throws Exception\ValidationsFailed
     * @see Transaction::sale()
     */
    public static function saleNoValidate($token, $transactionAttribs)
    {
        $result = self::sale($token, $transactionAttribs);
        return self::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * updates the creditcard record
     *
     * if calling this method in static context, $token
     * is the 2nd attribute. $token is not sent in object context.
     *
     * @access public
     * @param array $attributes
     * @param string $token (optional)
     * @return object Result\Successful or Result\Error
     */
    public static function update($token, $attributes)
    {
        Util::verifyKeys(self::updateSignature(), $attributes);
        self::_validateId($token);
        return self::_doUpdate('put', '/payment_methods/' . $token, array('creditCard' => $attributes));
    }

    /**
     * update a creditcard record, assuming validations will pass
     *
     * if calling this method in static context, $token
     * is the 2nd attribute. $token is not sent in object context.
     * returns a CreditCard object on success
     *
     * @access public
     * @param array $attributes
     * @param string $token
     * @return object CreditCard
     * @throws Exception\ValidationsFailed
     */
    public static function updateNoValidate($token, $attributes)
    {
        $result = self::update($token, $attributes);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public static function updateCreditCardUrl()
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::url", E_USER_NOTICE);
        return Configuration::merchantUrl() .
                '/payment_methods/all/update_via_transparent_redirect_request';
    }

    /**
     * update a customer from a TransparentRedirect operation
     *
     * @access public
     * @param $queryString
     * @return object
     */
    public static function updateFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );
        return self::_doUpdate(
            'post',
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

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

    public static function delete($token)
    {
        self::_validateId($token);
        Http::delete('/payment_methods/' . $token);
        return new Result\Successful();
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

        $subscriptionArray = array();
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
    public function isEqual($otherCreditCard)
    {
        return !($otherCreditCard instanceof CreditCard) ? false : $this->token === $otherCreditCard->token;
    }

    private static function baseOptions()
    {
        return array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'venmoSdkSession');
    }

    private static function baseSignature($options)
    {
         return array(
             'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
             'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
             'deviceData',
             array('options' => $options),
             array(
                 'billingAddress' => array(
                     'firstName',
                     'lastName',
                     'company',
                     'countryCodeAlpha2',
                     'countryCodeAlpha3',
                     'countryCodeNumeric',
                     'countryName',
                     'extendedAddress',
                     'locality',
                     'region',
                     'postalCode',
                     'streetAddress'
                 ),
             ),
         );
    }

    public static function createSignature()
    {
        $options = self::baseOptions();
        $options[] = "failOnDuplicatePaymentMethod";
        $signature = self::baseSignature($options);
        $signature[] = 'customerId';
        return $signature;
    }

    public static function updateSignature()
    {
         $signature = self::baseSignature(self::baseOptions());

         $updateExistingBillingSignature = array(
             array(
                 'options' => array(
                     'updateExisting'
                 )
             )
         );

         foreach($signature AS $key => $value) {
             if(is_array($value) and array_key_exists('billingAddress', $value)) {
                 $signature[$key]['billingAddress'] = array_merge_recursive($value['billingAddress'], $updateExistingBillingSignature);
             }
         }

         return $signature;
    }

    /**
     * sends the create request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function _doCreate($url, $params)
    {
        $response = Http::post($url, $params);

        return self::_verifyGatewayResponse($response);
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
     * verifies that a valid credit card token is being used
     * @ignore
     * @param string $token
     * @throws \InvalidArgumentException
     */
    private static function _validateId($token = null)
    {
        if (empty($token)) {
           throw new \InvalidArgumentException(
                   'expected credit card id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $token)) {
            throw new \InvalidArgumentException(
                    $token . ' is an invalid credit card id.'
                    );
        }
    }

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param $httpVerb
     * @param string $url
     * @param array $params
     * @return mixed
     */
    private static function _doUpdate($httpVerb, $url, $params)
    {
        $response = Http::$httpVerb($url, $params);
        return self::_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new CreditCard object and encapsulates
     * it inside a Result\Successful object, or
     * encapsulates a Errors object inside a Result\Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result\Successful or Result\Error
     * @throws Exception\Unexpected
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['creditCard'])) {
            // return a populated instance of Address
            return new Result\Successful(
                    self::factory($response['creditCard'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
            "Expected address or apiErrorResponse"
            );
        }
    }

    /**
     *  factory method: returns an instance of CreditCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @param $attributes
     * @return object instance of CreditCard
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
}
