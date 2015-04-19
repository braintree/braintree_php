<?php namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree PaymentMethodGateway module
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
class PaymentMethodGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Http($gateway->config);
    }


    public function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        return $this->_doCreate('/payment_methods', array('payment_method' => $attribs));
    }

    /**
     * find a PaymentMethod by token
     *
     * @access public
     * @param string $token payment method unique id
     * @return object CreditCard or PayPalAccount
     * @throws Exception\NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/any/' . $token;
            $response = $this->_http->get($path);
            if (isset($response['creditCard'])) {
                return CreditCard::factory($response['creditCard']);
            } else if (isset($response['paypalAccount'])) {
                return PayPalAccount::factory($response['paypalAccount']);
            } else if (isset($response['coinbaseAccount'])) {
                return CoinbaseAccount::factory($response['coinbaseAccount']);
            } else if (isset($response['applePayCard'])) {
                return ApplePayCard::factory($response['applePayCard']);
            } else if (is_array($response)) {
                return UnknownPaymentMethod::factory($response);
            }
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'payment method with token ' . $token . ' not found'
            );
        }

    }

    public function update($token, $attribs)
    {
        Util::verifyKeys(self::updateSignature(), $attribs);
        return $this->_doUpdate('/payment_methods/any/' . $token, array('payment_method' => $attribs));
    }

    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/any/' . $token;
        $this->_http->delete($path);
        return new Result\Successful();
    }

    private static function baseSignature($options)
    {
        $billingAddressSignature = AddressGateway::createSignature();
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
        $billingAddressSignature = AddressGateway::updateSignature();
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
     * @param string $subPath
     * @param array $params
     * @return mixed
     */
    public function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param string $subPath
     * @param array $params
     * @return mixed
     */
    public function _doUpdate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->put($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new CreditCard or PayPalAccount object
     * and encapsulates it inside a Result_Successful object, or
     * encapsulates a Errors object inside a Result_Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result_Successful or Result_Error
     * @throws Exception\Unexpected
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['creditCard'])) {
            // return a populated instance of CreditCard
            return new Result\Successful(
                CreditCard::factory($response['creditCard']),
                "paymentMethod"
            );
        } else if (isset($response['paypalAccount'])) {
            // return a populated instance of PayPalAccount
            return new Result\Successful(
                PayPalAccount::factory($response['paypalAccount']),
                "paymentMethod"
            );
        } else if (isset($response['coinbaseAccount'])) {
            // return a populated instance of CoinbaseAccount
            return new Result\Successful(
                CoinbaseAccount::factory($response['coinbaseAccount']),
                "paymentMethod"
            );
        } else if (isset($response['applePayCard'])) {
            // return a populated instance of ApplePayCard
            return new Result\Successful(
                ApplePayCard::factory($response['applePayCard']),
                "paymentMethod"
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else if (is_array($response)) {
            return new Result\Successful(
                UnknownPaymentMethod::factory($response),
                "paymentMethod"
            );
        } else {
            throw new Exception\Unexpected(
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
    private function _validateId($identifier = null, $identifierType = 'token')
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