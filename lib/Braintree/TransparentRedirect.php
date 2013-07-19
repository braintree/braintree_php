<?php

namespace Braintree;



/**
 * Braintree Transparent Redirect module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Static class providing methods to build Transparent Redirect urls
 *
 * The TransparentRedirect module provides methods to build the tr_data param
 * that must be submitted when using the transparent redirect API.
 * For more information
 * about transparent redirect, see (TODO).
 *
 * You must provide a redirectUrl to which the gateway will redirect the
 * user the action is complete.
 *
 * <code>
 *   $trData = TransparentRedirect::createCustomerData(array(
 *     'redirectUrl => 'http://example.com/redirect_back_to_merchant_site',
 *      ));
 * </code>
 *
 * In addition to the redirectUrl, any data that needs to be protected
 * from user tampering should be included in the trData.
 * For example, to prevent the user from tampering with the transaction
 * amount, include the amount in the trData.
 *
 * <code>
 *   $trData = TransparentRedirect::transactionData(array(
 *     'redirectUrl' => 'http://example.com/complete_transaction',
 *     'transaction' => array('amount' => '100.00'),
 *   ));
 *
 *  </code>
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 */
class TransparentRedirect
{
    // Request Kinds
    const CREATE_TRANSACTION = 'create_transaction';
    const CREATE_PAYMENT_METHOD = 'create_payment_method';
    const UPDATE_PAYMENT_METHOD = 'update_payment_method';
    const CREATE_CUSTOMER = 'create_customer';
    const UPDATE_CUSTOMER = 'update_customer';

    /**
     *
     * @ignore
     */
    private static $_transparentRedirectKeys = 'redirectUrl';
    private static $_createCustomerSignature;
    private static $_updateCustomerSignature;
    private static $_transactionSignature;
    private static $_createCreditCardSignature;
    private static $_updateCreditCardSignature;


    /**
     * @ignore
     * don't permit an explicit call of the constructor!
     * (like $t = new TransparentRedirect())
     */
    protected function __construct()
    {

    }

    /**
     * create signatures for different call types
     * @ignore
     */
    public static function init()
    {

        self::$_createCustomerSignature = array(
            self::$_transparentRedirectKeys,
            array('customer' => Customer::createSignature()),
            );
        self::$_updateCustomerSignature = array(
            self::$_transparentRedirectKeys,
            'customerId',
            array('customer' => Customer::updateSignature()),
            );
        self::$_transactionSignature = array(
            self::$_transparentRedirectKeys,
            array('transaction' => Transaction::createSignature()),
            );
        self::$_createCreditCardSignature = array(
            self::$_transparentRedirectKeys,
            array('creditCard' => CreditCard::createSignature()),
            );
        self::$_updateCreditCardSignature = array(
            self::$_transparentRedirectKeys,
            'paymentMethodToken',
            array('creditCard' => CreditCard::updateSignature()),
            );
    }

    public static function confirm($queryString)
    {
        $params = TransparentRedirect::parseAndValidateQueryString(
                $queryString
        );
        $confirmationKlasses = array(
            TransparentRedirect::CREATE_TRANSACTION => 'Transaction',
            TransparentRedirect::CREATE_CUSTOMER => 'Customer',
            TransparentRedirect::UPDATE_CUSTOMER => 'Customer',
            TransparentRedirect::CREATE_PAYMENT_METHOD => 'CreditCard',
            TransparentRedirect::UPDATE_PAYMENT_METHOD => 'CreditCard'
        );
        return call_user_func(array($confirmationKlasses[$params["kind"]], '_doCreate'),
            '/transparent_redirect_requests/' . $params['id'] . '/confirm',
            array()
        );
    }

    /**
     * returns the trData string for creating a credit card,
     * @param array $params
     * @return string
     */
    public static function createCreditCardData($params)
    {
        Util::verifyKeys(
                self::$_createCreditCardSignature,
                $params
                );
        $params["kind"] = TransparentRedirect::CREATE_PAYMENT_METHOD;
        return self::_data($params);
    }

    /**
     * returns the trData string for creating a customer.
     * @param array $params
     * @return string
     */
    public static function createCustomerData($params)
    {
        Util::verifyKeys(
                self::$_createCustomerSignature,
                $params
                );
        $params["kind"] = TransparentRedirect::CREATE_CUSTOMER;
        return self::_data($params);

    }

    public static function url()
    {
        return Configuration::merchantUrl() . "/transparent_redirect_requests";
    }

    /**
     * returns the trData string for creating a transaction
     *
     * @param array $params
     * @param $params
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function transactionData($params)
    {
        Util::verifyKeys(
                self::$_transactionSignature,
                $params
                );
        $params["kind"] = TransparentRedirect::CREATE_TRANSACTION;
        $transactionType = isset($params['transaction']['type']) ?
            $params['transaction']['type'] :
            null;
        if ($transactionType != Transaction::SALE && $transactionType != Transaction::CREDIT) {
           throw new \InvalidArgumentException(
                   'expected transaction[type] of sale or credit, was: ' .
                   $transactionType
                   );
        }

        return self::_data($params);
    }

    /**
     * Returns the trData string for updating a credit card.
     *
     *  The paymentMethodToken of the credit card to update is required.
     *
     * <code>
     * $trData = TransparentRedirect::updateCreditCardData(array(
     *     'redirectUrl' => 'http://example.com/redirect_here',
     *     'paymentMethodToken' => 'token123',
     *   ));
     * </code>
     *
     * @param array $params
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function updateCreditCardData($params)
    {
        Util::verifyKeys(
                self::$_updateCreditCardSignature,
                $params
                );
        if (!isset($params['paymentMethodToken'])) {
            throw new \InvalidArgumentException(
                   'expected params to contain paymentMethodToken.'
                   );
        }
        $params["kind"] = TransparentRedirect::UPDATE_PAYMENT_METHOD;
        return self::_data($params);
    }

    /**
     * Returns the trData string for updating a customer.
     *
     *  The customerId of the customer to update is required.
     *
     * <code>
     * $trData = TransparentRedirect::updateCustomerData(array(
     *     'redirectUrl' => 'http://example.com/redirect_here',
     *     'customerId' => 'customer123',
     *   ));
     * </code>
     *
     * @param array $params
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function updateCustomerData($params)
    {
        Util::verifyKeys(
                self::$_updateCustomerSignature,
                $params
                );
        if (!isset($params['customerId'])) {
            throw new \InvalidArgumentException(
                   'expected params to contain customerId of customer to update'
                   );
        }
        $params["kind"] = TransparentRedirect::UPDATE_CUSTOMER;
        return self::_data($params);
    }

    public static function parseAndValidateQueryString($queryString)
    {
        // parse the params into an array
        parse_str($queryString, $params);
        // remove the hash
        $queryStringWithoutHash = null;
        if(preg_match('/^(.*)&hash=[a-f0-9]+$/', $queryString, $match)) {
            $queryStringWithoutHash = $match[1];
        }

        if($params['http_status'] != '200') {
            $message = null;
            if(array_key_exists('bt_message', $params)) {
                $message = $params['bt_message'];
            }
            Util::throwStatusCodeException($params['http_status'], $message);
        }

        // recreate the hash and compare it
        if(self::_hash($queryStringWithoutHash) == $params['hash']) {
            return $params;
        } else {
            throw new Exception\ForgedQueryString();
        }
    }


    /**
     *
     * @ignore
     */
    private static function _data($params)
    {
        if (!isset($params['redirectUrl'])) {
            throw new \InvalidArgumentException(
                    'expected params to contain redirectUrl'
                    );
        }
        $params = self::_underscoreKeys($params);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $trDataParams = array_merge($params,
            array(
                'api_version' => Configuration::API_VERSION,
                'public_key'  => Configuration::publicKey(),
                'time'        => $now->format('YmdHis'),
            )
        );
        ksort($trDataParams);
        $trDataSegment = http_build_query($trDataParams, null, '&');
        $trDataHash = self::_hash($trDataSegment);
        return "$trDataHash|$trDataSegment";
    }

    private static function _underscoreKeys($array)
    {
        foreach($array as $key=>$value)
        {
            $newKey = Util::camelCaseToDelimiter($key, '_');
            unset($array[$key]);
            if (is_array($value))
            {
                $array[$newKey] = self::_underscoreKeys($value);
            }
            else
            {
                $array[$newKey] = $value;
            }
        }
        return $array;
    }

    /**
     * @ignore
     */
    private static function _hash($string)
    {
        return Digest::hexDigest($string);
    }

}
TransparentRedirect::init();
