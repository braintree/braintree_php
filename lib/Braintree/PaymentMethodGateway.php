<?php namespace Braintree;

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
        $this->_config->assertHasAccessTokenOrKeys();
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
     * @throws Exception_NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/any/' . $token;
            $response = $this->_http->get($path);
            if (isset($response['creditCard'])) {
                return CreditCard::factory($response['creditCard']);
            } else {
                if (isset($response['paypalAccount'])) {
                    return PayPalAccount::factory($response['paypalAccount']);
                } else {
                    if (isset($response['coinbaseAccount'])) {
                        return CoinbaseAccount::factory($response['coinbaseAccount']);
                    } else {
                        if (isset($response['applePayCard'])) {
                            return ApplePayCard::factory($response['applePayCard']);
                        } else {
                            if (isset($response['androidPayCard'])) {
                                return AndroidPayCard::factory($response['androidPayCard']);
                            } else {
                                if (is_array($response)) {
                                    return UnknownPaymentMethod::factory($response);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception_NotFound $e) {
            throw new Exception_NotFound(
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
        return new Result_Successful();
    }

    private static function baseSignature()
    {
        $billingAddressSignature = AddressGateway::createSignature();
        $optionsSignature = array(
            'failOnDuplicatePaymentMethod',
            'makeDefault',
            'verificationMerchantAccountId',
            'verifyCard'
        );
        return array(
            'billingAddressId',
            'cardholderName',
            'cvv',
            'deviceData',
            'expirationDate',
            'expirationMonth',
            'expirationYear',
            'number',
            'paymentMethodNonce',
            'token',
            array('options' => $optionsSignature),
            array('billingAddress' => $billingAddressSignature)
        );
    }

    public static function createSignature()
    {
        $signature = array_merge(self::baseSignature(), array('customerId'));
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
        $signature = array_merge(self::baseSignature(), array(
            'deviceSessionId',
            'venmoSdkPaymentMethodCode',
            'fraudMerchantId',
            array('billingAddress' => $billingAddressSignature)
        ));
        return $signature;
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
     * @throws Exception_Unexpected
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['creditCard'])) {
            // return a populated instance of CreditCard
            return new Result_Successful(
                CreditCard::factory($response['creditCard']),
                "paymentMethod"
            );
        } else {
            if (isset($response['paypalAccount'])) {
                // return a populated instance of PayPalAccount
                return new Result_Successful(
                    PayPalAccount::factory($response['paypalAccount']),
                    "paymentMethod"
                );
            } else {
                if (isset($response['coinbaseAccount'])) {
                    // return a populated instance of CoinbaseAccount
                    return new Result_Successful(
                        CoinbaseAccount::factory($response['coinbaseAccount']),
                        "paymentMethod"
                    );
                } else {
                    if (isset($response['applePayCard'])) {
                        // return a populated instance of ApplePayCard
                        return new Result_Successful(
                            ApplePayCard::factory($response['applePayCard']),
                            "paymentMethod"
                        );
                    } else {
                        if (isset($response['androidPayCard'])) {
                            // return a populated instance of AndroidPayCard
                            return new Result_Successful(
                                AndroidPayCard::factory($response['androidPayCard']),
                                "paymentMethod"
                            );
                        } else {
                            if (isset($response['apiErrorResponse'])) {
                                return new Result_Error($response['apiErrorResponse']);
                            } else {
                                if (is_array($response)) {
                                    return new Result_Successful(
                                        UnknownPaymentMethod::factory($response),
                                        "paymentMethod"
                                    );
                                } else {
                                    throw new Exception_Unexpected(
                                        'Expected payment method or apiErrorResponse'
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * verifies that a valid payment method identifier is being used
     *
     * @ignore
     * @param string $identifier
     * @param Optional $string $identifierType type of identifier supplied, default 'token'
     * @throws \InvalidArgumentException
     */
    private function _validateId($identifier = null, $identifierType = 'token')
    {
        if (empty($identifier)) {
            throw new \InvalidArgumentException(
                'expected payment method id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new \InvalidArgumentException(
                $identifier . ' is an invalid payment method ' . $identifierType . '.'
            );
        }
    }
}
