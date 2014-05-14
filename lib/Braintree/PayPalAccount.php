
<?php
/**
 * Braintree PayPalAccount module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree PayPalAccounts
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $email
 * @property-read string $token
 */
class Braintree_PayPalAccount extends Braintree
{

    public static function create($attribs)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        return self::_doCreate('/payment_methods', array('payment_method' => $attribs));
    }

    /**
     * find a paypalAccount by token
     *
     * @access public
     * @param string $token paypal accountunique id
     * @return object Braintree_PayPalAccount
     * @throws Braintree_Exception_NotFound
     */
    public static function find($token)
    {
        self::_validateId($token);
        try {
            $response = Braintree_Http::get('/payment_methods/paypal_account/'.$token);
            return self::factory($response['paypalAccount']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
                'paypal account with token ' . $token . ' not found'
            );
        }

    }

    /**
     * updates the paypalAccount record
     *
     * if calling this method in static context, $token
     * is the 2nd attribute. $token is not sent in object context.
     *
     * @access public
     * @param array $attributes
     * @param string $token (optional)
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public static function update($token, $attributes)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        self::_validateId($token);
        return self::_doUpdate('put', '/payment_methods/paypal_account/' . $token, array('paypalAccount' => $attributes));
    }

    public static function delete($token)
    {
        self::_validateId($token);
        Braintree_Http::delete('/payment_methods/paypal_account/' . $token);
        return new Braintree_Result_Successful();
    }

    private static function baseSignature($options)
    {
         return array(
             'customerId', 'paymentMethodNonce',
             array('options' => $options),
         );
    }

    public static function createSignature()
    {
        $options = array('makeDefault', 'failOnDuplicatePaymentMethod');
        $signature = self::baseSignature($options);
        return $signature;
    }

    public static function updateSignature()
    {
        return array('token');
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
        $response = Braintree_Http::post($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    private static function _doUpdate($httpVerb, $url, $params)
    {
        $response = Braintree_Http::$httpVerb($url, $params);
        return self::_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Braintree_PayPalAccount object and encapsulates
     * it inside a Braintree_Result_Successful object, or
     * encapsulates a Braintree_Errors object inside a Result_Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result_Successful or Result_Error
     * @throws Braintree_Exception_Unexpected
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['paypalAccount'])) {
            // return a populated instance of Braintree_PayPalAccount
            return new Braintree_Result_Successful(
                    self::factory($response['paypalAccount'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            'Expected paypal account or apiErrorResponse'
            );
        }
    }

    /**
     *  factory method: returns an instance of Braintree_PayPalAccount
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree_PayPalAccount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * verifies that a valid paypal account identifier is being used
     * @ignore
     * @param string $identifier
     * @param Optional $string $identifierType type of identifier supplied, default 'token'
     * @throws InvalidArgumentException
     */
    private static function _validateId($identifier = null, $identifierType = 'token')
    {
        if (empty($identifier)) {
           throw new InvalidArgumentException(
                   'expected paypal account id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                    $identifier . ' is an invalid paypal account ' . $identifierType . '.'
                    );
        }
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
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $paypalAccountAttribs array of paypalAccount data
     * @return none
     */
    protected function _initialize($paypalAccountAttribs)
    {
        // set the attributes
        $this->_attributes = $paypalAccountAttribs;
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

}
