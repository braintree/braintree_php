<?php
namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree TransactionGateway processor
 * Creates and manages transactions.
 *
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Transactions, see {@link http://www.braintreepayments.com/gateway/transaction-api http://www.braintreepaymentsolutions.com/gateway/transaction-api}
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
final class TransactionGateway
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

    public function cloneTransaction($transactionId, $attribs)
    {
        Util::verifyKeys(self::cloneSignature(), $attribs);

        return $this->_doCreate('/transactions/'.$transactionId.'/clone', array('transactionClone' => $attribs));
    }

    /**
     * @ignore
     *
     * @param array $attribs
     *
     * @return object
     */
    private function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);

        return $this->_doCreate('/transactions', array('transaction' => $attribs));
    }

    /**
     * @ignore
     *
     * @param array $attribs
     *
     * @return object
     *
     * @throws Exception\ValidationError
     */
    private function createNoValidate($attribs)
    {
        $result = $this->create($attribs);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
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
                '/transactions/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }
    /**
     * @param none
     *
     * @return string
     */
    public function createTransactionUrl()
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::url', E_USER_NOTICE);

        return $this->_config->baseUrl().$this->_config->merchantPath().
                '/transactions/all/create_via_transparent_redirect_request';
    }

    public static function cloneSignature()
    {
        return array('amount', 'channel', array('options' => array('submitForSettlement')));
    }

    /**
     * creates a full array signature of a valid gateway request.
     *
     * @return array gateway request signature format
     */
    public static function createSignature()
    {
        return array(
            'amount',
            'billingAddressId',
            'channel',
            'customerId',
            'deviceData',
            'deviceSessionId',
            'fraudMerchantId',
            'merchantAccountId',
            'orderId',
            'paymentMethodNonce',
            'paymentMethodToken',
            'purchaseOrderNumber',
            'recurring',
            'serviceFeeAmount',
            'shippingAddressId',
            'taxAmount',
            'taxExempt',
            'threeDSecureToken',
            'type',
            'venmoSdkPaymentMethodCode',
            array(
                'creditCard' => array(
                    'token', 'cardholderName', 'cvv', 'expirationDate',
                    'expirationMonth', 'expirationYear', 'number'
                )
            ),
            array(
                'customer' => array(
                    'id', 'company', 'email', 'fax', 'firstName',
                    'lastName', 'phone', 'website'
                )
            ),
            array(
                'billing' => array(
                    'firstName', 'lastName', 'company', 'countryName',
                    'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
                    'extendedAddress', 'locality', 'postalCode', 'region',
                    'streetAddress'
                )
            ),
            array(
                'shipping' => array(
                    'firstName', 'lastName', 'company', 'countryName',
                    'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
                    'extendedAddress', 'locality', 'postalCode', 'region',
                    'streetAddress'
                )
            ),
            array(
                'options' => array(
                    'holdInEscrow',
                    'storeInVault',
                    'storeInVaultOnSuccess',
                    'submitForSettlement',
                    'addBillingAddressToPaymentMethod',
                    'venmoSdkSession',
                    'storeShippingAddressInVault',
                    'payeeEmail',
                    array(
                        'three_d_secure' => array('required'),
                    ),
                    array(
                        'paypal' => array(
                            'payeeEmail',
                            'customField',
                            'description'
                        )
                    ),
                    array(
                        'amexRewards' => array(
                            'requestId',
                            'points',
                            'currencyAmount',
                            'currencyIsoCode'
                        )
                    )
                )
            ),
            array(
                'customFields' => array('_anyKey_')
            ),
            array(
                'descriptor' => array('name', 'phone', 'url')
            ),
            array(
                'paypalAccount' => array('payeeEmail')
            ),
            array(
                'apple_pay_card' => array(
                    'number', 'cardholder_name', 'cryptogram',
                    'expiration_month', 'expiration_year'
                )
            ),
            array(
                'industry' => array(
                    'industryType',
                    array(
                        'data' => array(
                            'folioNumber',
                            'checkInDate',
                            'checkOutDate',
                            'travelPackage',
                            'departureDate',
                            'lodgingCheckInDate',
                            'lodgingCheckOutDate',
                            'lodgingName',
                            'roomRate'
                        )
                    )
                )
            )
        );
    }

    /**
     * @param array $attribs
     *
     * @return object
     */
    public function credit($attribs)
    {
        return $this->create(array_merge($attribs, array('type' => Transaction::CREDIT)));
    }

    /**
     * @param array $attribs
     *
     * @return object
     *
     * @throws Exception\ValidationError
     */
    public function creditNoValidate($attribs)
    {
        $result = $this->credit($attribs);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     *
     */
    public function find($id)
    {
        $this->_validateId($id);

        try {
            $path = $this->_config->merchantPath().'/transactions/'.$id;
            $response = $this->_http->get($path);

            return Transaction::factory($response['transaction']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound('transaction with id '.$id.' not found');
        }
    }
    /**
     * new sale.
     *
     * @param array $attribs
     *
     * @return array
     */
    public function sale($attribs)
    {
        return $this->create(array_merge(array('type' => Transaction::SALE), $attribs));
    }

    /**
     * roughly equivalent to the ruby bang method.
     *
     * @param array $attribs
     *
     * @return array
     *
     * @throws Exception\ValidationsFailed
     */
    public function saleNoValidate($attribs)
    {
        $result = $this->sale($attribs);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * Returns a ResourceCollection of transactions matching the search query.
     *
     * If <b>query</b> is a string, the search will be a basic search.
     * If <b>query</b> is a hash, the search will be an advanced search.
     * For more detailed information and examples, see {@link http://www.braintreepayments.com/gateway/transaction-api#searching http://www.braintreepaymentsolutions.com/gateway/transaction-api}
     *
     * @param mixed $query   search query
     * @param array $options options such as page number
     *
     * @return object ResourceCollection
     *
     * @throws InvalidArgumentException
     */
    public function search($query)
    {
        $criteria = array();

        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $path = $this->_config->merchantPath().'/transactions/advanced_search_ids';
        $response = $this->_http->post($path, array('search' => $criteria));

        if (!array_key_exists('searchResults', $response)) {
            throw new Exception\DownForMaintenance();
        }

        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array($query),
        );

        return new ResourceCollection($response, $pager);
    }

    public function fetch($query, $ids)
    {
        $criteria = array();

        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $criteria['ids'] = TransactionSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath().'/transactions/advanced_search';
        $response = $this->_http->post($path, array('search' => $criteria));

        return Util::extractattributeasarray(
            $response['creditCardTransactions'],
            'transaction'
        );
    }

    /**
     * void a transaction by id.
     *
     * @param string $id transaction id
     *
     * @return object Result\Successful|Braintree\Result\Error
     */
    public function void($transactionId)
    {
        $this->_validateId($transactionId);

        $path = $this->_config->merchantPath().'/transactions/'.$transactionId.'/void';
        $response = $this->_http->put($path);

        return $this->_verifyGatewayResponse($response);
    }
    /**
     *
     */
    public function voidNoValidate($transactionId)
    {
        $result = $this->void($transactionId);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    public function submitForSettlement($transactionId, $amount = null)
    {
        $this->_validateId($transactionId);

        $path = $this->_config->merchantPath().'/transactions/'.$transactionId.'/submit_for_settlement';
        $response = $this->_http->put($path, array('transaction' => array('amount' => $amount)));

        return $this->_verifyGatewayResponse($response);
    }

    public function submitForSettlementNoValidate($transactionId, $amount = null)
    {
        $result = $this->submitForSettlement($transactionId, $amount);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    public function holdInEscrow($transactionId)
    {
        $this->_validateId($transactionId);

        $path = $this->_config->merchantPath().'/transactions/'.$transactionId.'/hold_in_escrow';
        $response = $this->_http->put($path, array());

        return $this->_verifyGatewayResponse($response);
    }

    public function releaseFromEscrow($transactionId)
    {
        $this->_validateId($transactionId);

        $path = $this->_config->merchantPath().'/transactions/'.$transactionId.'/release_from_escrow';
        $response = $this->_http->put($path, array());

        return $this->_verifyGatewayResponse($response);
    }

    public function cancelRelease($transactionId)
    {
        $this->_validateId($transactionId);

        $path = $this->_config->merchantPath().'/transactions/'.$transactionId.'/cancel_release';
        $response = $this->_http->put($path, array());

        return $this->_verifyGatewayResponse($response);
    }

    public function refund($transactionId, $amount = null)
    {
        self::_validateId($transactionId);

        $params = array('transaction' => array('amount' => $amount));
        $path = $this->_config->merchantPath().'/transactions/'.$transactionId.'/refund';
        $response = $this->_http->post($path, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * sends the create request to the gateway.
     *
     * @ignore
     *
     * @param var   $subPath
     * @param array $params
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
     * verifies that a valid transaction id is being used.
     *
     * @ignore
     *
     * @param string transaction id
     *
     * @throws InvalidArgumentException
     */
    private function _validateId($id = null)
    {
        if (is_null($id)) {
            throw new InvalidArgumentException('expected transaction id to be set');
        }

        if (!preg_match('/^[0-9a-z]+$/', $id)) {
            throw new InvalidArgumentException($id.' is an invalid transaction id.');
        }
    }

    /**
     * generic method for validating incoming gateway responses.
     *
     * creates a new Transaction object and encapsulates
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
        if (isset($response['transaction'])) {
            // return a populated instance of Transaction
            return new Result\Successful(Transaction::factory($response['transaction']));
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected('Expected transaction or apiErrorResponse');
        }
    }
}
