<?php
namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree PaymentMethodGateway module.
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethods.
 *
 * <b>== More information ==</b>
 *
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
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
     * find a PaymentMethod by token.
     *
     * @param string $token payment method unique id
     *
     * @return object CreditCard or PayPalAccount
     *
     * @throws Exception\NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath().'/payment_methods/any/'.$token;
            $response = $this->_http->get($path);
            if (isset($response['creditCard'])) {
                return CreditCard::factory($response['creditCard']);
            } elseif (isset($response['paypalAccount'])) {
                return PayPalAccount::factory($response['paypalAccount']);
            } elseif (isset($response['coinbaseAccount'])) {
                return CoinbaseAccount::factory($response['coinbaseAccount']);
            } elseif (isset($response['applePayCard'])) {
                return ApplePayCard::factory($response['applePayCard']);
            } elseif (isset($response['androidPayCard'])) {
                return AndroidPayCard::factory($response['androidPayCard']);
            } elseif (isset($response['europeBankAccount'])) {
                return EuropeBankAccount::factory($response['europeBankAccount']);
            } elseif (is_array($response)) {
                return UnknownPaymentMethod::factory($response);
            }
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'payment method with token '.$token.' not found'
            );
        }
    }

    public function update($token, $attribs)
    {
        Util::verifyKeys(self::updateSignature(), $attribs);

        return $this->_doUpdate('/payment_methods/any/'.$token, array('payment_method' => $attribs));
    }

    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath().'/payment_methods/any/'.$token;
        $this->_http->delete($path);

        return new Result\Successful();
    }

    private static function baseSignature()
    {
        $billingAddressSignature = AddressGateway::createSignature();
        $optionsSignature = array(
            'failOnDuplicatePaymentMethod',
            'makeDefault',
            'verificationMerchantAccountId',
            'verifyCard',
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
            array('billingAddress' => $billingAddressSignature),
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
                'updateExisting',
            ),
        ));
        $signature = array_merge(self::baseSignature(), array(
            'deviceSessionId',
            'venmoSdkPaymentMethodCode',
            'fraudMerchantId',
            array('billingAddress' => $billingAddressSignature),
        ));

        return $signature;
    }

    /**
     * sends the create request to the gateway.
     *
     * @ignore
     *
     * @param string $subPath
     * @param array  $params
     *
     * @return mixed
     */
    public function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath().$subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * sends the update request to the gateway.
     *
     * @ignore
     *
     * @param string $subPath
     * @param array  $params
     *
     * @return mixed
     */
    public function _doUpdate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath().$subPath;
        $response = $this->_http->put($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses.
     *
     * creates a new CreditCard or PayPalAccount object
     * and encapsulates it inside a Result\Successful object, or
     * encapsulates a Errors object inside a Result\Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     *
     * @param array $response gateway response values
     *
     * @return object Result\Successful or Result\Error
     *
     * @throws Exception\Unexpected
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['creditCard'])) {
            // return a populated instance of CreditCard
            return new Result\Successful(
                CreditCard::factory($response['creditCard']),
                'paymentMethod'
            );
        } elseif (isset($response['paypalAccount'])) {
            // return a populated instance of PayPalAccount
            return new Result\Successful(
                PayPalAccount::factory($response['paypalAccount']),
                'paymentMethod'
            );
        } elseif (isset($response['coinbaseAccount'])) {
            // return a populated instance of CoinbaseAccount
            return new Result\Successful(
                CoinbaseAccount::factory($response['coinbaseAccount']),
                'paymentMethod'
            );
        } elseif (isset($response['applePayCard'])) {
            // return a populated instance of ApplePayCard
            return new Result\Successful(
                ApplePayCard::factory($response['applePayCard']),
                'paymentMethod'
            );
        } elseif (isset($response['androidPayCard'])) {
            // return a populated instance of AndroidPayCard
            return new Result\Successful(
                AndroidPayCard::factory($response['androidPayCard']),
                'paymentMethod'
            );
        } elseif (isset($response['europeBankAccount'])) {
            // return a populated instance of EuropeBankAccount
            return new Result\Successful(
                EuropeBankAccount::factory($response['europeBankAccount']),
                'paymentMethod'
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } elseif (is_array($response)) {
            return new Result\Successful(
                UnknownPaymentMethod::factory($response),
                'paymentMethod'
            );
        } else {
            throw new Exception\Unexpected(
                'Expected payment method or apiErrorResponse'
            );
        }
    }

    /**
     * verifies that a valid payment method identifier is being used.
     *
     * @ignore
     *
     * @param string   $identifier
     * @param Optional $string     $identifierType type of identifier supplied, default 'token'
     *
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
                    $identifier.' is an invalid payment method '.$identifierType.'.'
                    );
        }
    }
}
