<?php
/**
 * Braintree PaymentMethod module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethods
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class Braintree_PaymentMethod extends Braintree
{

    public static function create($attribs)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        return self::_doCreate('/payment_methods', array('payment_method' => $attribs));
    }

    /**
     * find a PaymentMethod by token
     *
     * @access public
     * @param string $token payment method unique id
     * @return object Braintree_CreditCard or Braintree_PayPalAccount
     * @throws Braintree_Exception_NotFound
     */
    public static function find($token)
    {
        self::_validateId($token);
        try {
            $response = Braintree_Http::get('/payment_methods/any/'.$token);
            if (isset($response['creditCard'])) {
                return Braintree_CreditCard::factory($response['creditCard']);
            } else if (isset($response['paypalAccount'])) {
                return Braintree_PayPalAccount::factory($response['paypalAccount']);
            } else if (is_array($response)) {
                return Braintree_UnknownPaymentMethod::factory($response);
            }
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
                'payment method with token ' . $token . ' not found'
            );
        }

    }

    public static function update($token, $attribs)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attribs);
        return self::_doUpdate('/payment_methods/any/' . $token, array('payment_method' => $attribs));
    }

    public static function delete($token)
    {
        self::_validateId($token);
        Braintree_Http::delete('/payment_methods/any/' . $token);
        return new Braintree_Result_Successful();
    }

    private static function baseSignature($options)
    {
        $billingAddressSignature = Braintree_Address::createSignature();
        return array(
            'customerId',
            'paymentMethodNonce',
            'token',
            'billingAddressId',
            'deviceData',
            array('options' => $options),
            array('billingAddress' => $billingAddressSignature)
        );
    }

    public static function createSignature()
    {
        $options = array(
            'makeDefault',
            'verifyCard',
            'failOnDuplicatePaymentMethod',
            'verificationMerchantAccountId'
        );
        $signature = self::baseSignature($options);
        return $signature;
    }

    public static function updateSignature()
    {
        $billingAddressSignature = Braintree_Address::updateSignature();
        array_push($billingAddressSignature, array(
            'options' => array(
                'updateExisting'
            )
        ));
        return array(
            'billingAddressId',
            'cardholderName',
            'cvv',
            'deviceSessionId',
            'expirationDate',
            'expirationMonth',
            'expirationYear',
            'number',
            'token',
            'venmoSdkPaymentMethodCode',
            'deviceData',
            'fraudMerchantId',
            'paymentMethodNonce',
            array('options' => array(
                'makeDefault',
                'verificationMerchantAccountId',
                'verifyCard',
                'venmoSdkSession'
            )),
            array('billingAddress' => $billingAddressSignature)
        );
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
    public static function _doUpdate($url, $params)
    {
        $response = Braintree_Http::put($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Braintree_CreditCard or Braintree_PayPalAccount object
     * and encapsulates it inside a Braintree_Result_Successful object, or
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
        if (isset($response['creditCard'])) {
            // return a populated instance of Braintree_CreditCard
            return new Braintree_Result_Successful(
                Braintree_CreditCard::factory($response['creditCard']),
                "paymentMethod"
            );
        } else if (isset($response['paypalAccount'])) {
            // return a populated instance of Braintree_PayPalAccount
            return new Braintree_Result_Successful(
                Braintree_PayPalAccount::factory($response['paypalAccount']),
                "paymentMethod"
            );
        } else if (isset($response['applePayCard'])) {
            // return a populated instance of Braintree_ApplePayCard
            return new Braintree_Result_Successful(
                Braintree_ApplePayCard::factory($response['applePayCard']),
                "paymentMethod"
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else if (is_array($response)) {
            return new Braintree_Result_Successful(
                Braintree_UnknownPaymentMethod::factory($response),
                "paymentMethod"
            );
        } else {
            throw new Braintree_Exception_Unexpected(
            'Expected payment method or apiErrorResponse'
            );
        }
    }

    /**
     * verifies that a valid payment method identifier is being used
     * @ignore
     * @param string $identifier
     * @param Optional $string $identifierType type of identifier supplied, default 'token'
     * @throws InvalidArgumentException
     */
    private static function _validateId($identifier = null, $identifierType = 'token')
    {
        if (empty($identifier)) {
           throw new InvalidArgumentException(
                   'expected payment method id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                    $identifier . ' is an invalid payment method ' . $identifierType . '.'
                    );
        }
    }
}
