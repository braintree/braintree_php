<?php

namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree TransactionGateway processor
 * Creates and manages transactions
 *
 * // phpcs:ignore Generic.Files.LineLength
 * For more detailed information on Transactions, see {@link https://developer.paypal.com/braintree/docs/reference/response/transaction/php our developer docs}
 */

class TransactionGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }

    /**
     * Create a new transaction by copying all the attributes, except amount, of the original transaction
     *
     * Instead of cloning transactions, a better practice in most cases is to use the Vault to save and reuse payment method or customer information
     *
     * @param string $transactionId to be cloned
     * @param mixed  $attribs       containing additional request parameters
     *
     * @see PaymentMethodGateway
     * @see CustomerGateway
     *
     * @return Result\Successful|Result\Error
     */
    public function cloneTransaction($transactionId, $attribs)
    {
        Util::verifyKeys(self::cloneSignature(), $attribs);
        $this->_checkForDeprecatedAttributes($attribs);
        return $this->_doCreate('/transactions/' . $transactionId . '/clone', ['transactionClone' => $attribs]);
    }

    private function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        $this->_checkForDeprecatedAttributes($attribs);
        $attribs = Util::replaceKey($attribs, 'googlePayCard', 'androidPayCard');
        return $this->_doCreate('/transactions', ['transaction' => $attribs]);
    }

    private function createNoValidate($attribs)
    {
        $this->_checkForDeprecatedAttributes($attribs);
        $result = $this->create($attribs);
        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function cloneSignature()
    {
        return ['amount', 'channel', ['options' => ['submitForSettlement']]];
    }

    // NEXT_MAJOR_VERSION remove threeDSecureToken, venmoSdkPaymentMethodCode, and venmoSdkSession
    // threeDSecureToken has been deprecated in favor of threeDSecureAuthenticationId
    // The old venmo SDK class has been deprecated
    /**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function createSignature()
    {
        return [
            'accountFundingTransaction',
            'amount',
            ['applePayCard' =>
                [
                    'cardholderName',
                    'cryptogram',
                    'eciIndicator',
                    'expirationMonth',
                    'expirationYear',
                    'number'
                ]
            ],
            ['billing' =>
                [
                    'firstName', 'lastName', 'company', 'countryName',
                    'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
                    'extendedAddress', 'locality', 'phoneNumber', ['internationalPhone' => ['countryCode', 'nationalNumber']], 'postalCode', 'region',
                    'streetAddress'],
            ],
            'billingAddressId',
            'channel',
            ['creditCard' =>
                [
                    'cardholderName',
                    'cvv',
                    'expirationDate',
                    'expirationMonth',
                    'expirationYear',
                    ['networkTokenizationAttributes' => ['cryptogram', 'ecommerceIndicator', 'tokenRequestorId']],
                    'number',
                    ['paymentReaderCardDetails' => ['encryptedCardData', 'keySerialNumber']],
                    'token',
                ],
            ],
            ['customer' =>
                [
                    'id', 'company', 'email', 'fax', 'firstName',
                    'lastName', 'phone', ['internationalPhone' => ['countryCode', 'nationalNumber']], 'website'],
            ],
            'customerId',
            ['customFields' => ['_anyKey_']],
            ['descriptor' => ['name', 'phone', 'url']],
            'deviceData',
            'discountAmount',
            'exchangeRateQuoteId',
            ['externalVault' =>
                ['status' , 'previousNetworkTransactionId'],
            ],
            'foreignRetailer',
            ['googlePayCard' =>
                [
                    'cryptogram',
                    'eciIndicator',
                    'expirationMonth',
                    'expirationYear',
                    'googleTransactionId',
                    'number',
                    'sourceCardLastFour',
                    'sourceCardType'
                ]
            ],
            ['industry' =>
                ['industryType',
                    ['data' =>
                        [
                            'arrivalDate',
                            'folioNumber',
                            'checkInDate',
                            'checkOutDate',
                            'countryCode',
                            'dateOfBirth',
                            'travelPackage',
                            'departureDate',
                            'lodgingCheckInDate',
                            'lodgingCheckOutDate',
                            'lodgingName',
                            'roomRate',
                            'roomTax',
                            'passengerFirstName',
                            'passengerLastName',
                            'passengerMiddleInitial',
                            'passengerTitle',
                            'issuedDate',
                            'travelAgencyName',
                            'travelAgencyCode',
                            'ticketNumber',
                            'issuingCarrierCode',
                            'customerCode',
                            'fareAmount',
                            'feeAmount',
                            'taxAmount',
                            'restrictedTicket',
                            'noShow',
                            'advancedDeposit',
                            'fireSafe',
                            'propertyPhone',
                            ['legs' =>
                                [
                                    'conjunctionTicket',
                                    'exchangeTicket',
                                    'couponNumber',
                                    'serviceClass',
                                    'carrierCode',
                                    'fareBasisCode',
                                    'flightNumber',
                                    'departureDate',
                                    'departureAirportCode',
                                    'departureTime',
                                    'arrivalAirportCode',
                                    'arrivalTime',
                                    'stopoverPermitted',
                                    'fareAmount',
                                    'feeAmount',
                                    'taxAmount',
                                    'endorsementOrRestrictions'
                                ]
                            ],
                            ['additionalCharges' =>
                                [
                                    'kind',
                                    'amount'
                                ]
                            ],
                            'ticketIssuerAddress'
                        ]
                    ]
                ]
            ],
            ['installments' => ['count']],
            ['lineItems' =>
                [
                    'commodityCode',
                    'description',
                    'discountAmount',
                    'imageUrl',
                    'kind',
                    'name',
                    'productCode',
                    'quantity',
                    'taxAmount',
                    'totalAmount',
                    'unitAmount',
                    'unitOfMeasure',
                    'unitTaxAmount',
                    'upcCode',
                    'upcType',
                    'url'
                ]
            ],
            'merchantAccountId',
            ['options' =>
                [
                    'addBillingAddressToPaymentMethod',
                    'payeeId',
                    'payeeEmail',
                    'skipAdvancedFraudChecking',
                    'skipAvs',
                    'skipCvv',
                    'storeInVault',
                    'storeInVaultOnSuccess',
                    'storeShippingAddressInVault',
                    'submitForSettlement',
                    'venmoSdkSession',  // Deprecated

                    ['creditCard' =>
                        ['accountType',
                        'processDebitAsCredit']
                    ],
                    ['threeDSecure' =>
                        ['required']
                    ],
                    ['paypal' =>
                        [
                            'customField',
                            'description',
                            'payeeEmail',
                            'payeeId',
                            'recipientEmail',
                            ['recipientPhone' =>
                                ['countryCode', 'nationalNumber']
                            ],
                            ['supplementaryData' => ['_anyKey_']],
                        ]
                    ],
                    ['amexRewards' =>
                        [
                            'requestId',
                            'points',
                            'currencyAmount',
                            'currencyIsoCode'
                        ]
                    ],
                    ['venmo' =>
                        [
                            'profileId'
                        ]
                    ],
                    ['processingOverrides' =>
                        [
                            'customerEmail',
                            'customerFirstName',
                            'customerLastName',
                            'customerTaxIdentifier'
                        ]
                    ]
                ],
            ],
            'orderId',
            'paymentMethodNonce',
            'paymentMethodToken',
            ['paypalAccount' => ['payeeId', 'payeeEmail', 'payerId', 'paymentId']],
            'processingMerchantCategoryCode',
            'productSku',
            'purchaseOrderNumber',
            'recurring',
            ['riskData' =>
                [
                    'customerBrowser', 'customerIp', 'customerDeviceId',
                    'customerLocationZip', 'customerTenure'],
            ],
            'scaExemption',
            'serviceFeeAmount',
            'sharedCustomerId',
            'sharedPaymentMethodToken',
            'sharedPaymentMethodNonce',
            'sharedShippingAddressId',
            'sharedBillingAddressId',
            ['shipping' =>
                [
                    'firstName', 'lastName', 'company', 'countryName',
                    'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
                    'extendedAddress', 'locality', 'phoneNumber', ['internationalPhone' => ['countryCode', 'nationalNumber']], 'postalCode', 'region',
                    'shippingMethod', 'streetAddress'],
            ],
            'shippingAddressId',
            'shippingAmount',
            'shippingTaxAmount',
            'shipsFromPostalCode',
            'taxAmount',
            'taxExempt',
            ['threeDSecurePassThru' =>
                [
                    'eciFlag',
                    'cavv',
                    'xid',
                    'threeDSecureVersion',
                    'authenticationResponse',
                    'directoryResponse',
                    'cavvAlgorithm',
                    'dsTransactionId'],
            ],
            'threeDSecureToken', //Deprecated
            'threeDSecureAuthenticationId',
            'transactionSource',
            [   'transfer' => [
                    'type',
                ],
            ],
            'type',
            ['usBankAccount' =>
                [
                    'achMandateText',
                    'achMandateAcceptedAt'
                ]
            ],
            'venmoSdkPaymentMethodCode',  // Deprecated
            [
                'paymentFacilitator' => [
                    'paymentFacilitatorId',
                    [
                        'subMerchant' => [
                            'referenceNumber',
                            'taxId',
                            'legalName',
                            [
                                'address' => [
                                    'streetAddress',
                                    'locality',
                                    'region',
                                    'countryCodeAlpha2',
                                    'postalCode',
                                    [
                                        'internationalPhone' => [
                                            'countryCode',
                                            'nationalNumber',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

             [
                'transfer' => [
                    'type',
                    [
                        'sender' => [
                            'firstName',
                            'lastName',
                            'accountReferenceNumber',
                            'taxId',
                            [
                                'address' => [
                                    'streetAddress',
                                    'extendedAddress',
                                    'locality',
                                    'region',
                                    'countryCodeAlpha2',
                                    'postalCode',
                                    [
                                        'internationalPhone' => [
                                            'countryCode',
                                            'nationalNumber',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                    ],
                    [
                        'receiver' => [
                            'firstName',
                            'lastName',
                            'accountReferenceNumber',
                            'taxId',
                            [
                                'address' => [
                                    'streetAddress',
                                    'extendedAddress',
                                    'locality',
                                    'region',
                                    'countryCodeAlpha2',
                                    'postalCode',
                                    [
                                        'internationalPhone' => [
                                            'countryCode',
                                            'nationalNumber',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * creates a full array signature of a valid package tracking request
     *
     * @return array package tracking request signature format
     */
    public static function packageTrackingRequestSignature()
    {
        return
        [
            'carrier',
            'trackingNumber',
            'notifyPayer',
            [
                'lineItems' =>
                    [
                        'commodityCode',
                        'description',
                        'discountAmount',
                        'imageUrl',
                        'kind',
                        'name',
                        'productCode',
                        'quantity',
                        'taxAmount',
                        'totalAmount',
                        'unitAmount',
                        'unitOfMeasure',
                        'unitTaxAmount',
                        'upcCode',
                        'upcType',
                        'url'
                    ]
            ],
        ];
    }

    /**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function submitForSettlementSignature()
    {
        return ['orderId', ['descriptor' => ['name', 'phone', 'url']],
            ['industry' =>
                ['industryType',
                    ['data' =>
                        [
                            'advancedDeposit',
                            'arrivalDate',
                            'checkInDate',
                            'checkOutDate',
                            'countryCode',
                            'customerCode',
                            'dateOfBirth',
                            'departureDate',
                            'fareAmount',
                            'feeAmount',
                            'fireSafe',
                            'folioNumber',
                            'issuedDate',
                            'issuingCarrierCode',
                            'lodgingCheckInDate',
                            'lodgingCheckOutDate',
                            'lodgingName',
                            'noShow',
                            'passengerFirstName',
                            'passengerLastName',
                            'passengerMiddleInitial',
                            'passengerTitle',
                            'propertyPhone',
                            'restrictedTicket',
                            'roomRate',
                            'roomTax',
                            'taxAmount',
                            'ticketIssuerAddress',
                            'ticketNumber',
                            'travelAgencyCode',
                            'travelAgencyName',
                            'travelPackage',
                            ['legs' =>
                                [
                                    'arrivalAirportCode',
                                    'arrivalTime',
                                    'carrierCode',
                                    'conjunctionTicket',
                                    'couponNumber',
                                    'departureAirportCode',
                                    'departureDate',
                                    'departureTime',
                                    'endorsementOrRestrictions',
                                    'exchangeTicket',
                                    'fareAmount',
                                    'fareBasisCode',
                                    'feeAmount',
                                    'flightNumber',
                                    'serviceClass',
                                    'stopoverPermitted',
                                    'taxAmount',
                                ]
                            ],
                            ['additionalCharges' =>
                                [
                                    'amount',
                                    'kind'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            ['lineItems' =>
                [
                    'commodityCode',
                    'description',
                    'discountAmount',
                    'imageUrl',
                    'kind',
                    'name',
                    'productCode',
                    'quantity',
                    'taxAmount',
                    'totalAmount',
                    'unitAmount',
                    'unitOfMeasure',
                    'unitTaxAmount',
                    'upcCode',
                    'upcType',
                    'url'
                ]
            ],
            'discountAmount',
            'purchaseOrderNumber',
            'shippingAmount',
            'shippingTaxAmount',
            'shipsFromPostalCode',
            'taxAmount',
            'taxExempt',
        ];
    }

    /**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function submitForPartialSettlementSignature()
    {
        return array_merge(
            self::submitForSettlementSignature(),
            ['finalCapture']
        );
    }

    /**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function updateDetailsSignature()
    {
        return ['amount', 'orderId', ['descriptor' => ['name', 'phone', 'url']]];
    }

    /**
     * creates a full array signature of a valid gateway request
     *
     * @return array gateway request signature format
     */
    public static function refundSignature()
    {
        return [
            'amount',
            'merchantAccountId',
            'orderId'
        ];
    }

    /**
     * Request a credit to a payment method
     *
     * @param array $attribs containing request parameters
     *
     * @return Result\Successful|Result\Error
     */
    public function credit($attribs)
    {
        return $this->create(array_merge($attribs, ['type' => Transaction::CREDIT]));
    }

    /**
     * Request a credit to a payment method. Returns either a Transaction or error
     *
     * @param array $attribs containing request parameters
     *
     * @return Transaction|Result\Error
     */
    public function creditNoValidate($attribs)
    {
        $result = $this->credit($attribs);
        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * Retrieve transaction information given its ID
     *
     * @param string $id unique identifier of the transaction
     *
     * @return Transaction|Exception\NotFound
     */
    public function find($id)
    {
        $this->_validateId($id);
        try {
            $path = $this->_config->merchantPath() . '/transactions/' . $id;
            $response = $this->_http->get($path);
            return Transaction::factory($response['transaction']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'transaction with id ' . $id . ' not found'
            );
        }
    }
    /**
     * Request a new sale
     *
     * @param array $attribs (Note: $recurring param is deprecated. Use $transactionSource instead)
     *
     * @return Result\Successful|Result\Error
     */
    public function sale($attribs)
    {
        // phpcs:ignore
        if (array_key_exists('recurring', $attribs)) {
            trigger_error('$recurring is deprecated, use $transactionSource instead', E_USER_DEPRECATED);
        }
        return $this->create(array_merge(['type' => Transaction::SALE], $attribs));
    }

    /**
     * Request a new sale. Returns a Transaction object instead of a Result
     *
     * @param mixed $attribs containing any request parameters
     *
     * @return Transaction|Result\Error
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
     * // phpcs:ignore Generic.Files.LineLength
     * For more detailed information and examples, see {@link https://developer.paypal.com/braintree/docs/reference/request/transaction/search/php our developer docs}
     *
     * @param mixed $query search query
     *
     * @return ResourceCollection
     */
    public function search($query)
    {
        $criteria = [];
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $path = $this->_config->merchantPath() . '/transactions/advanced_search_ids';
        $response = $this->_http->post($path, ['search' => $criteria]);
        // phpcs:ignore
        if (array_key_exists('searchResults', $response)) {
            $pager = [
                'object' => $this,
                'method' => 'fetch',
                'methodArgs' => [$query]
                ];

            return new ResourceCollection($response, $pager);
        } else {
            throw new Exception\RequestTimeout();
        }
    }

    /**
     * Function to fetch results in building paged reults
     *
     * @param mixed $query including method arguments
     * @param array $ids   to use in searching
     *
     * @return array
     */
    public function fetch($query, $ids)
    {
        $criteria = [];
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = TransactionSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath() . '/transactions/advanced_search';
        $response = $this->_http->post($path, ['search' => $criteria]);

        // phpcs:ignore
        if (array_key_exists('creditCardTransactions', $response)) {
            return Util::extractattributeasarray(
                $response['creditCardTransactions'],
                'transaction'
            );
        } else {
            throw new Exception\RequestTimeout();
        }
    }

    /**
     * Adjusts the authorization amount of a transaction
     *
     * @param string $transactionId unique identifier
     * @param string $amount        tp be adjusted
     *
     * @return Result\Successful|Result\Error
     */
    public function adjustAuthorization($transactionId, $amount)
    {
        self::_validateId($transactionId);
        $params = ['amount' => $amount];
        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/adjust_authorization';
        $response = $this->_http->put($path, ['transaction' => $params]);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * void a transaction by id
     *
     * @param string $transactionId unique identifier
     *
     * @return Result\Successful|Result\Error
     */
    public function void($transactionId)
    {
        $this->_validateId($transactionId);

        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/void';
        $response = $this->_http->put($path);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * void a transaction by id. Returns a Transaction instead of Result\Successful
     *
     * @param string $transactionId unique identifier
     *
     * @return Transaction|Result\Error
     */
    public function voidNoValidate($transactionId)
    {
        $result = $this->void($transactionId);
        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * Submits  an authorized transaction be captured and submitted for settlement.
     *
     * @param string      $transactionId uniquq identifier
     * @param string|null $amount        to be submitted for settlement
     * @param array       $attribs       containing any additional request parameters
     *
     * @return Result\Successful|Result\Error
     */
    public function submitForSettlement($transactionId, $amount = null, $attribs = [])
    {
        $this->_validateId($transactionId);
        Util::verifyKeys(self::submitForSettlementSignature(), $attribs);
        $attribs['amount'] = $amount;

        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/submit_for_settlement';
        $response = $this->_http->put($path, ['transaction' => $attribs]);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * Submits  an authorized transaction be captured and submitted for settlement. Returns a Transaction object on success
     *
     * @param string      $transactionId uniquq identifier
     * @param string|null $amount        to be submitted for settlement
     * @param array       $attribs       containing any additional request parameters
     *
     * @return Transaction|Exception
     */
    public function submitForSettlementNoValidate($transactionId, $amount = null, $attribs = [])
    {
        $result = $this->submitForSettlement($transactionId, $amount, $attribs);
        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * Update certain details for a transaction that has been submitted for settlement
     *
     * @param string $transactionId to be updated
     * @param array  $attribs       attributes to be updated in the request
     *
     * @return Result\Successful|Result\Error
     */
    public function updateDetails($transactionId, $attribs = [])
    {
        $this->_validateId($transactionId);
        Util::verifyKeys(self::updateDetailsSignature(), $attribs);

        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/update_details';
        $response = $this->_http->put($path, ['transaction' => $attribs]);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * Supplement the transaction with package tracking details
     *
     * @param string $transactionId to be updated
     * @param array  $attribs       package tracking request attributes
     *
     * @return Result\Successful|Result\Error
     */
    public function packageTracking($transactionId, $attribs = [])
    {
        $this->_validateId($transactionId);
        Util::verifyKeys(self::packageTrackingRequestSignature(), $attribs);

        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/shipments';
        $response = $this->_http->post($path, ['shipment' => $attribs]);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * Settle multiple partial amounts against the same authorization
     *
     * @param string $transactionId unque identifier of the transaction to be submitted for settlement
     * @param string $amount        optional
     * @param mixed  $attribs       any additional request parameters
     *
     * @return Result\Successful|Exception\NotFound
     */
    public function submitForPartialSettlement($transactionId, $amount, $attribs = [])
    {
        $this->_validateId($transactionId);
        Util::verifyKeys(self::submitForPartialSettlementSignature(), $attribs);
        $attribs['amount'] = $amount;

        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/submit_for_partial_settlement';
        $response = $this->_http->post($path, ['transaction' => $attribs]);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * Request a refund to a payment method
     *
     * @param string $transactionId     unque identifier of the transaction to be refunded
     * @param mixed  $amount_or_options if a string amount, the amount to be refunded, if array of options, additional request parameters
     *
     * @return Result\Successful|Exception\NotFound
     */
    public function refund($transactionId, $amount_or_options = null)
    {
        self::_validateId($transactionId);

        if (gettype($amount_or_options) == "array") {
            $options = $amount_or_options;
        } else {
            $options = [
                "amount" => $amount_or_options
            ];
        }
        Util::verifyKeys(self::refundSignature(), $options);

        $params = ['transaction' => $options];
        $path = $this->_config->merchantPath() . '/transactions/' . $transactionId . '/refund';
        $response = $this->_http->post($path, $params);
        return $this->_verifyGatewayResponse($response);
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * verifies that a valid transaction id is being used
     *
     * @param string transaction id

     * @throws InvalidArgumentException
     *
     * @return null
     */
    private function _validateId($id = null)
    {
        if (empty($id)) {
            throw new InvalidArgumentException(
                'expected transaction id to be set'
            );
        }
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Transaction object and encapsulates
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
        if (isset($response['transaction'])) {
            // return a populated instance of Transaction
            return new Result\Successful(
                Transaction::factory($response['transaction'])
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
                "Expected transaction or apiErrorResponse"
            );
        }
    }

    private function _checkForDeprecatedAttributes($attributes)
    {
        if (isset($attributes['threeDSecureToken'])) {
            trigger_error('threeDSecureToken has been deprecated. Please use threeDSecureAuthenticationId instead.', E_USER_DEPRECATED);
        }
        if (isset($attributes['venmoSdkSession']) || isset($attributes['venmoSdkPaymentMethodCode'])) {
            trigger_error('The Venmo SDK integration is Unsupported. Please update your integration to use Pay with Venmo instead.', E_USER_DEPRECATED);
        }
    }
}
