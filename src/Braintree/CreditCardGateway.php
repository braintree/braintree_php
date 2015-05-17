<?php
namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree CreditCardGateway module
 * Creates and manages Braintree CreditCards.
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on CreditCards, see {@link http://www.braintreepayments.com/gateway/credit-card-api http://www.braintreepaymentsolutions.com/gateway/credit-card-api}<br />
 * For more detailed information on CreditCard verifications, see {@link http://www.braintreepayments.com/gateway/credit-card-verification-api http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api}
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class CreditCardGateway
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

        return $this->_doCreate('/payment_methods', array('credit_card' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a CreditCard object instead of a Result.
     *
     * @param array $attribs
     *
     * @return object
     *
     * @throws Exception\ValidationError
     */
    public function createNoValidate($attribs)
    {
        $result = $this->create($attribs);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     * create a customer from a TransparentRedirect operation.
     *
     * @param array $attribs
     *
     * @return object
     */
    public function createFromTransparentRedirect($queryString)
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::confirm', E_USER_NOTICE);
        $params = TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );

        return $this->_doCreate(
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    /**
     * @param none
     *
     * @return string
     */
    public function createCreditCardUrl()
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::url', E_USER_NOTICE);

        return $this->_config->baseUrl().$this->_config->merchantPath().
                '/payment_methods/all/create_via_transparent_redirect_request';
    }

    /**
     * returns a ResourceCollection of expired credit cards.
     *
     * @return object ResourceCollection
     */
    public function expired()
    {
        $path = $this->_config->merchantPath().'/payment_methods/all/expired_ids';
        $response = $this->_http->post($path);
        $pager = array(
            'object' => $this,
            'method' => 'fetchExpired',
            'methodArgs' => array(),
        );

        return new ResourceCollection($response, $pager);
    }

    public function fetchExpired($ids)
    {
        $path = $this->_config->merchantPath().'/payment_methods/all/expired';
        $response = $this->_http->post($path, array('search' => array('ids' => $ids)));

        return Util::extractattributeasarray(
            $response['paymentMethods'],
            'creditCard'
        );
    }
    /**
     * returns a ResourceCollection of credit cards expiring between start/end.
     *
     * @return object ResourceCollection
     */
    public function expiringBetween($startDate, $endDate)
    {
        $queryPath = $this->_config->merchantPath().'/payment_methods/all/expiring_ids?start='.date('mY', $startDate).'&end='.date('mY', $endDate);
        $response = $this->_http->post($queryPath);
        $pager = array(
            'object' => $this,
            'method' => 'fetchExpiring',
            'methodArgs' => array($startDate, $endDate),
        );

        return new ResourceCollection($response, $pager);
    }

    public function fetchExpiring($startDate, $endDate, $ids)
    {
        $queryPath = $this->_config->merchantPath().'/payment_methods/all/expiring?start='.date('mY', $startDate).'&end='.date('mY', $endDate);
        $response = $this->_http->post($queryPath, array('search' => array('ids' => $ids)));

        return Util::extractAttributeAsArray(
            $response['paymentMethods'],
            'creditCard'
        );
    }

    /**
     * find a creditcard by token.
     *
     * @param string $token credit card unique id
     *
     * @return object CreditCard
     *
     * @throws Exception\NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath().'/payment_methods/credit_card/'.$token;
            $response = $this->_http->get($path);

            return CreditCard::factory($response['creditCard']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'credit card with token '.$token.' not found'
            );
        }
    }

    /**
     * Convert a payment method nonce to a credit card.
     *
     * @param string $nonce payment method nonce
     *
     * @return object CreditCard
     *
     * @throws Exception\NotFound
     */
    public function fromNonce($nonce)
    {
        $this->_validateId($nonce, 'nonce');
        try {
            $path = $this->_config->merchantPath().'/payment_methods/from_nonce/'.$nonce;
            $response = $this->_http->get($path);

            return CreditCard::factory($response['creditCard']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'credit card with nonce '.$nonce.' locked, consumed or not found'
            );
        }
    }

    /**
     * create a credit on the card for the passed transaction.
     *
     * @param array $attribs
     *
     * @return object Result\Successful or Result\Error
     */
    public function credit($token, $transactionAttribs)
    {
        $this->_validateId($token);

        return Transaction::credit(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a credit on this card, assuming validations will pass.
     *
     * returns a Transaction object on success
     *
     * @param array $attribs
     *
     * @return object Transaction
     *
     * @throws Exception\ValidationError
     */
    public function creditNoValidate($token, $transactionAttribs)
    {
        $result = $this->credit($token, $transactionAttribs);

        return Util::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * create a new sale for the current card.
     *
     * @param string $token
     * @param array  $transactionAttribs
     *
     * @return object Result\Successful or Result\Error
     *
     * @see Transaction::sale()
     */
    public function sale($token, $transactionAttribs)
    {
        $this->_validateId($token);

        return Transaction::sale(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a new sale using this card, assuming validations will pass.
     *
     * returns a Transaction object on success
     *
     * @param array  $transactionAttribs
     * @param string $token
     *
     * @return object Transaction
     *
     * @throws Exception\ValidationsFailed
     *
     * @see Transaction::sale()
     */
    public function saleNoValidate($token, $transactionAttribs)
    {
        $result = $this->sale($token, $transactionAttribs);

        return Util::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * updates the creditcard record.
     *
     * if calling this method in context, $token
     * is the 2nd attribute. $token is not sent in object context.
     *
     * @param array  $attributes
     * @param string $token      (optional)
     *
     * @return object Result\Successful or Result\Error
     */
    public function update($token, $attributes)
    {
        Util::verifyKeys(self::updateSignature(), $attributes);
        $this->_validateId($token);

        return $this->_doUpdate('put', '/payment_methods/credit_card/'.$token, array('creditCard' => $attributes));
    }

    /**
     * update a creditcard record, assuming validations will pass.
     *
     * if calling this method in context, $token
     * is the 2nd attribute. $token is not sent in object context.
     * returns a CreditCard object on success
     *
     * @param array  $attributes
     * @param string $token
     *
     * @return object CreditCard
     *
     * @throws Exception\ValidationsFailed
     */
    public function updateNoValidate($token, $attributes)
    {
        $result = $this->update($token, $attributes);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     * @param none
     *
     * @return string
     */
    public function updateCreditCardUrl()
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::url', E_USER_NOTICE);

        return $this->_config->baseUrl().$this->_config->merchantPath().
                '/payment_methods/all/update_via_transparent_redirect_request';
    }

    /**
     * update a customer from a TransparentRedirect operation.
     *
     * @param array $attribs
     *
     * @return object
     */
    public function updateFromTransparentRedirect($queryString)
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::confirm', E_USER_NOTICE);
        $params = TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );

        return $this->_doUpdate(
            'post',
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath().'/payment_methods/credit_card/'.$token;
        $this->_http->delete($path);

        return new Result\Successful();
    }

    private static function baseOptions()
    {
        return array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'verificationAmount', 'venmoSdkSession');
    }

    private static function baseSignature($options)
    {
        return array(
             'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
             'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
             'deviceData', 'fraudMerchantId', 'paymentMethodNonce',
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
                     'streetAddress',
                 ),
             ),
         );
    }

    public static function createSignature()
    {
        $options = self::baseOptions();
        $options[] = 'failOnDuplicatePaymentMethod';
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
                     'updateExisting',
                 ),
             ),
         );

        foreach ($signature as $key => $value) {
            if (is_array($value) and array_key_exists('billingAddress', $value)) {
                $signature[$key]['billingAddress'] = array_merge_recursive($value['billingAddress'], $updateExistingBillingSignature);
            }
        }

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
     * verifies that a valid credit card identifier is being used.
     *
     * @ignore
     *
     * @param string   $identifier
     * @param Optional $string     $identifierType type of identifier supplied, default "token"
     *
     * @throws InvalidArgumentException
     */
    private function _validateId($identifier = null, $identifierType = 'token')
    {
        if (empty($identifier)) {
            throw new InvalidArgumentException(
                   'expected credit card id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                    $identifier.' is an invalid credit card '.$identifierType.'.'
                    );
        }
    }

    /**
     * sends the update request to the gateway.
     *
     * @ignore
     *
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     */
    private function _doUpdate($httpVerb, $subPath, $params)
    {
        $fullPath = $this->_config->merchantPath().$subPath;
        $response = $this->_http->$httpVerb($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses.
     *
     * creates a new CreditCard object and encapsulates
     * it inside a Result\Successful object, or
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
            // return a populated instance of Address
            return new Result\Successful(
                    CreditCard::factory($response['creditCard'])
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
            'Expected address or apiErrorResponse'
            );
        }
    }
}
