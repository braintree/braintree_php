<?php
namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree CustomerGateway module
 * Creates and manages Customers.
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Customers, see {@link http://www.braintreepayments.com/gateway/customer-api http://www.braintreepaymentsolutions.com/gateway/customer-api}
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class CustomerGateway
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

    public function all()
    {
        $path = $this->_config->merchantPath().'/customers/advanced_search_ids';
        $response = $this->_http->post($path);
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array(array()),
            );

        return new ResourceCollection($response, $pager);
    }

    public function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria['ids'] = CustomerSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath().'/customers/advanced_search';
        $response = $this->_http->post($path, array('search' => $criteria));

        return Util::extractattributeasarray(
            $response['customers'],
            'customer'
        );
    }

    /**
     * Creates a customer using the given +attributes+. If <tt>:id</tt> is not passed,
     * the gateway will generate it.
     *
     * <code>
     *   $result = Customer::create(array(
     *     'first_name' => 'John',
     *     'last_name' => 'Smith',
     *     'company' => 'Smith Co.',
     *     'email' => 'john@smith.com',
     *     'website' => 'www.smithco.com',
     *     'fax' => '419-555-1234',
     *     'phone' => '614-555-1234'
     *   ));
     *   if($result->success) {
     *     echo 'Created customer ' . $result->customer->id;
     *   } else {
     *     echo 'Could not create customer, see result->errors';
     *   }
     * </code>
     *
     * @param array $attribs
     *
     * @return object Result, either Successful or Error
     */
    public function create($attribs = array())
    {
        Util::verifyKeys(self::createSignature(), $attribs);

        return $this->_doCreate('/customers', array('customer' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Customer object instead of a Result.
     *
     * @param array $attribs
     *
     * @return object
     *
     * @throws Exception\ValidationError
     */
    public function createNoValidate($attribs = array())
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
                '/customers/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }

    /**
     * @param none
     *
     * @return string
     */
    public function createCustomerUrl()
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::url', E_USER_NOTICE);

        return $this->_config->baseUrl().$this->_config->merchantPath().
                '/customers/all/create_via_transparent_redirect_request';
    }

    /**
     * creates a full array signature of a valid create request.
     *
     * @return array gateway create request format
     */
    public static function createSignature()
    {
        $creditCardSignature = CreditCardGateway::createSignature();
        unset($creditCardSignature[array_search('customerId', $creditCardSignature)]);
        $signature = array(
            'id', 'company', 'email', 'fax', 'firstName',
            'lastName', 'phone', 'website', 'deviceData',
            'deviceSessionId', 'fraudMerchantId', 'paymentMethodNonce',
            array('creditCard' => $creditCardSignature),
            array('customFields' => array('_anyKey_')),
            );

        return $signature;
    }

    /**
     * creates a full array signature of a valid update request.
     *
     * @return array update request format
     */
    public static function updateSignature()
    {
        $creditCardSignature = CreditCardGateway::updateSignature();

        foreach ($creditCardSignature as $key => $value) {
            if (is_array($value) and array_key_exists('options', $value)) {
                array_push($creditCardSignature[$key]['options'], 'updateExistingToken');
            }
        }

        $signature = array(
            'id', 'company', 'email', 'fax', 'firstName',
            'lastName', 'phone', 'website', 'deviceData',
            'deviceSessionId', 'fraudMerchantId', 'paymentMethodNonce',
            array('creditCard' => $creditCardSignature),
            array('customFields' => array('_anyKey_')),
            );

        return $signature;
    }

    /**
     * find a customer by id.
     *
     * @param string id customer Id
     *
     * @return object Customer|boolean false
     */
    public function find($id)
    {
        $this->_validateId($id);

        try {
            $path = $this->_config->merchantPath().'/customers/'.$id;
            $response = $this->_http->get($path);

            return Customer::factory($response['customer']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound('customer with id '.$id.' not found');
        }
    }

    /**
     * credit a customer for the passed transaction.
     *
     * @param array $attribs
     *
     * @return object Result\Successful or Result\Error
     */
    public function credit($customerId, $transactionAttribs)
    {
        $this->_validateId($customerId);

        return Transaction::credit(array_merge($transactionAttribs, array('customerId' => $customerId)));
    }

    /**
     * credit a customer, assuming validations will pass.
     *
     * returns a Transaction object on success
     *
     * @param array $attribs
     *
     * @return object Transaction
     *
     * @throws Exception\ValidationError
     */
    public function creditNoValidate($customerId, $transactionAttribs)
    {
        $result = $this->credit($customerId, $transactionAttribs);

        return Util::returnObjectOrThrowException('Braintree\Transaction', $result);
    }

    /**
     * delete a customer by id.
     *
     * @param string $customerId
     */
    public function delete($customerId)
    {
        $this->_validateId($customerId);
        $path = $this->_config->merchantPath().'/customers/'.$customerId;
        $this->_http->delete($path);

        return new Result\Successful();
    }

    /**
     * create a new sale for a customer.
     *
     * @param string $customerId
     * @param array  $transactionAttribs
     *
     * @return object Result\Successful or Result\Error
     *
     * @see Transaction::sale()
     */
    public function sale($customerId, $transactionAttribs)
    {
        $this->_validateId($customerId);

        return Transaction::sale(
                array_merge($transactionAttribs,
                        array('customerId' => $customerId)
                        )
                );
    }

    /**
     * create a new sale for a customer, assuming validations will pass.
     *
     * returns a Transaction object on success
     *
     * @param string $customerId
     * @param array  $transactionAttribs
     *
     * @return object Transaction
     *
     * @throws Exception\ValidationsFailed
     *
     * @see Transaction::sale()
     */
    public function saleNoValidate($customerId, $transactionAttribs)
    {
        $result = $this->sale($customerId, $transactionAttribs);

        return Util::returnObjectOrThrowException('Braintree\Transaction', $result);
    }

    /**
     * Returns a ResourceCollection of customers matching the search query.
     *
     * If <b>query</b> is a string, the search will be a basic search.
     * If <b>query</b> is a hash, the search will be an advanced search.
     * For more detailed information and examples, see {@link http://www.braintreepayments.com/gateway/customer-api#searching http://www.braintreepaymentsolutions.com/gateway/customer-api}
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
            $result = $term->toparam();
            if (is_null($result) || empty($result)) {
                throw new InvalidArgumentException('Operator must be provided');
            }

            $criteria[$term->name] = $term->toparam();
        }

        $path = $this->_config->merchantPath().'/customers/advanced_search_ids';
        $response = $this->_http->post($path, array('search' => $criteria));
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array($query),
            );

        return new ResourceCollection($response, $pager);
    }

    /**
     * updates the customer record.
     *
     * if calling this method in static context, customerId
     * is the 2nd attribute. customerId is not sent in object context.
     *
     * @param array  $attributes
     * @param string $customerId (optional)
     *
     * @return object Result\Successful or Result\Error
     */
    public function update($customerId, $attributes)
    {
        Util::verifyKeys(self::updateSignature(), $attributes);
        $this->_validateId($customerId);

        return $this->_doUpdate(
            'put',
            '/customers/'.$customerId,
            array('customer' => $attributes)
        );
    }

    /**
     * update a customer record, assuming validations will pass.
     *
     * if calling this method in static context, customerId
     * is the 2nd attribute. customerId is not sent in object context.
     * returns a Customer object on success
     *
     * @param array  $attributes
     * @param string $customerId
     *
     * @return object Customer
     *
     * @throws Exception\ValidationsFailed
     */
    public function updateNoValidate($customerId, $attributes)
    {
        $result = $this->update($customerId, $attributes);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     * @param none
     *
     * @return string
     */
    public function updateCustomerUrl()
    {
        trigger_error('DEPRECATED: Please use TransparentRedirectRequest::url', E_USER_NOTICE);

        return $this->_config->baseUrl().$this->_config->merchantPath().
                '/customers/all/update_via_transparent_redirect_request';
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
                '/customers/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }

    /* instance methods */

    /**
     * sets instance properties from an array of values.
     *
     * @ignore
     *
     * @param array $customerAttribs array of customer data
     *
     * @return none
     */
    protected function _initialize($customerAttribs)
    {
        // set the attributes
        $this->_attributes = $customerAttribs;

        // map each address into its own object
        $addressArray = array();
        if (isset($customerAttribs['addresses'])) {
            foreach ($customerAttribs['addresses'] as $address) {
                $addressArray[] = Address::factory($address);
            }
        }
        $this->_set('addresses', $addressArray);

        // map each creditCard into its own object
        $creditCardArray = array();
        if (isset($customerAttribs['creditCards'])) {
            foreach ($customerAttribs['creditCards'] as $creditCard) {
                $creditCardArray[] = CreditCard::factory($creditCard);
            }
        }
        $this->_set('creditCards', $creditCardArray);

        // map each paypalAccount into its own object
        $paypalAccountArray = array();
        if (isset($customerAttribs['paypalAccounts'])) {
            foreach ($customerAttribs['paypalAccounts'] as $paypalAccount) {
                $paypalAccountArray[] = PayPalAccount::factory($paypalAccount);
            }
        }
        $this->_set('paypalAccounts', $paypalAccountArray);

        // map each applePayCard into its own object
        $applePayCardArray = array();
        if (isset($customerAttribs['applePayCards'])) {
            foreach ($customerAttribs['applePayCards'] as $applePayCard) {
                $applePayCardArray[] = ApplePayCard::factory($applePayCard);
            }
        }
        $this->_set('applePayCards', $applePayCardArray);

        // map each androidPayCard into its own object
        $androidPayCardArray = array();
        if (isset($customerAttribs['androidPayCards'])) {
            foreach ($customerAttribs['androidPayCards'] as $androidPayCard) {
                $androidPayCardArray[] = AndroidPayCard::factory($androidPayCard);
            }
        }
        $this->_set('androidPayCards', $androidPayCardArray);
    }

    /**
     * returns a string representation of the customer.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__.'['.
                Util::attributesToString($this->_attributes).']';
    }

    /**
     * returns false if comparing object is not a Customer,
     * or is a Customer with a different id.
     *
     * @param object $otherCust customer to compare against
     *
     * @return bool
     */
    public function isEqual($otherCust)
    {
        return !($otherCust instanceof Customer) ? false : $this->id === $otherCust->id;
    }

    /**
     * returns an array containt all of the customer's payment methods.
     *
     * @return array
     */
    public function paymentMethods()
    {
        return array_merge($this->creditCards, $this->paypalAccounts, $this->applePayCards, $this->androidPayCards);
    }

    /**
     * returns the customer's default payment method.
     *
     * @return object CreditCard | PayPalAccount | ApplePayCard | AndroidPayCard
     */
    public function defaultPaymentMethod()
    {
        $defaultPaymentMethods = array_filter($this->paymentMethods(), 'Braintree\Customer::_defaultPaymentMethodFilter');

        return current($defaultPaymentMethods);
    }

    public static function _defaultPaymentMethodFilter($paymentMethod)
    {
        return $paymentMethod->isDefault();
    }

    /* private class properties  */

    /**
     * @var array registry of customer data
     */
    protected $_attributes = array(
        'addresses'   => '',
        'company'     => '',
        'creditCards' => '',
        'email'       => '',
        'fax'         => '',
        'firstName'   => '',
        'id'          => '',
        'lastName'    => '',
        'phone'       => '',
        'createdAt'   => '',
        'updatedAt'   => '',
        'website'     => '',
        );

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
     * verifies that a valid customer id is being used.
     *
     * @ignore
     *
     * @param string customer id
     *
     * @throws InvalidArgumentException
     */
    private function _validateId($id = null)
    {
        if (is_null($id)) {
            throw new InvalidArgumentException('expected customer id to be set');
        }

        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException($id.' is an invalid customer id.');
        }
    }

    /* private class methods */

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
    private function _doUpdate($httpVerb, $subPath, $params)
    {
        $fullPath = $this->_config->merchantPath().$subPath;
        $response = $this->_http->$httpVerb($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses.
     *
     * creates a new Customer object and encapsulates
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
        if (isset($response['customer'])) {
            // return a populated instance of Customer
            return new Result\Successful(
                    Customer::factory($response['customer'])
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
            'Expected customer or apiErrorResponse'
            );
        }
    }
}
