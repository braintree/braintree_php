<?php

namespace Braintree;

use Braintree\HttpHelpers\HttpClientAware;
use InvalidArgumentException;

/**
 * Braintree PayPalAccountGateway module
 *
 * Manages Braintree PayPalAccounts
 *
 * For more detailed information on PayPal Accounts, see {@link https://developer.paypal.com/braintree/docs/reference/response/paypal-account our developer docs}<br />
 */
class PayPalAccountGateway
{
    use HttpClientAware;

    private $_gateway;
    private $_config;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }


    /**
     * Find a paypalAccount by token
     *
     * @param string $token paypal accountunique id
     *
     * @throws Exception\NotFound
     *
     * @return PayPalAccount
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/paypal_account/' . $token;
            $response = $this->_http->get($path);
            return PayPalAccount::factory($response['paypalAccount']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'paypal account with token ' . $token . ' not found'
            );
        }
    }

    /**
     * updates the paypalAccount record
     *
     * if calling this method in context, $token
     * is the 2nd attribute. $token is not sent in object context.
     *
     * @param string $token      (optional)
     * @param array  $attributes including request parameters
     *
     * @return Result\Successful or Result\Error
     */
    public function update($token, $attributes)
    {
        Util::verifyKeys(self::updateSignature(), $attributes);
        $this->_validateId($token);
        return $this->_doUpdate('put', '/payment_methods/paypal_account/' . $token, ['paypalAccount' => $attributes]);
    }

    /**
     * Delete a PayPal Account record
     *
     * @param string $token paypal account identifier
     *
     * @return Result
     */
    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/paypal_account/' . $token;
        $this->_http->delete($path);
        return new Result\Successful();
    }

    /**
     * create a new sale for the current PayPal account
     *
     * @param string $token              paypal account identifier
     * @param array  $transactionAttribs containing request parameters
     *
     * @return Result\Successful|Result\Error
     */
    public function sale($token, $transactionAttribs)
    {
        $this->_validateId($token);
        return Transaction::sale(
            array_merge(
                $transactionAttribs,
                ['paymentMethodToken' => $token]
            )
        );
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public static function updateSignature()
    {
        return [
            'token',
            ['options' => ['makeDefault']]
        ];
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    private function _doUpdate($httpVerb, $subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->$httpVerb($fullPath, $params);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new PayPalAccount object and encapsulates
     * it inside a Result\Successful object, or
     * encapsulates a Errors object inside a Result\Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @param array $response gateway response values
     *
     * @throws Exception\Unexpected
     *
     * @return Result\Successful|Result\Error
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['paypalAccount'])) {
            // return a populated instance of PayPalAccount
            return new Result\Successful(
                PayPalAccount::factory($response['paypalAccount'])
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
                'Expected paypal account or apiErrorResponse'
            );
        }
    }

    /**
     * verifies that a valid paypal account identifier is being used
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
                'expected paypal account id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                $identifier . ' is an invalid paypal account ' . $identifierType . '.'
            );
        }
    }
}
