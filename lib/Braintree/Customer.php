<?php

namespace Braintree;

/**
 * Braintree Customer module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates and manages Customers
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Customers, see {@link http://www.braintreepayments.com/gateway/customer-api http://www.braintreepaymentsolutions.com/gateway/customer-api}
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read array  $addresses
 * @property-read string $company
 * @property-read string $createdAt
 * @property-read array  $creditCards
 * @property-read array  $customFields custom fields passed with the request
 * @property-read string $email
 * @property-read string $fax
 * @property-read string $firstName
 * @property-read string $id
 * @property-read string $lastName
 * @property-read string $phone
 * @property-read string $updatedAt
 * @property-read string $website
 */
class Customer extends Braintree
{
    public static function all()
    {
        $response = Http::post('/customers/advanced_search_ids');
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetch',
            'methodArgs' => array(array())
            );

        return new ResourceCollection($response, $pager);
    }

    /**
     * @param IsNode[] $query
     * @param Int[] $ids
     * @return object[]
     */
    public static function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = CustomerSearch::ids()->in($ids)->toparam();
        $response = Http::post('/customers/advanced_search', array('search' => $criteria));

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
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array())
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        return self::_doCreate('/customers', array('customer' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Customer object instead of a Result
     *
     * @access public
     * @param array $attribs
     * @return object
     * @throws Exception\ValidationsFailed
     */
    public static function createNoValidate($attribs = array())
    {
        $result = self::create($attribs);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * create a customer from a TransparentRedirect operation
     *
     * @access public
     * @param $queryString
     * @return object
     */
    public static function createFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = TransparentRedirect::parseAndValidateQueryString(
                $queryString
                );
        return self::_doCreate(
                '/customers/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }

    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public static function createCustomerUrl()
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::url", E_USER_NOTICE);
        return Configuration::merchantUrl() .
                '/customers/all/create_via_transparent_redirect_request';
    }


    /**
     * creates a full array signature of a valid create request
     * @return array gateway create request format
     */
    public static function createSignature()
    {

        $creditCardSignature = CreditCard::createSignature();
        unset($creditCardSignature['customerId']);
        $signature = array(
            'id', 'company', 'email', 'fax', 'firstName',
            'lastName', 'phone', 'website', 'deviceData',
            array('creditCard' => $creditCardSignature),
            array('customFields' => array('_anyKey_')),
            );
        return $signature;
    }

    /**
     * creates a full array signature of a valid update request
     * @return array update request format
     */
    public static function updateSignature()
    {
        $creditCardSignature = CreditCard::updateSignature();

        foreach($creditCardSignature AS $key => $value) {
            if(is_array($value) and array_key_exists('options', $value)) {
                array_push($creditCardSignature[$key]['options'], 'updateExistingToken');
            }
        }

        $signature = array(
            'id', 'company', 'email', 'fax', 'firstName',
            'lastName', 'phone', 'website', 'deviceData',
            array('creditCard' => $creditCardSignature),
            array('customFields' => array('_anyKey_')),
            );
        return $signature;
    }


    /**
     * find a customer by id
     *
     * @access public
     * @param string $id customer Id
     * @return object Customer
     * @throws Exception\NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Http::get('/customers/'.$id);
            return self::factory($response['customer']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
            'customer with id ' . $id . ' not found'
            );
        }

    }

    /**
     * credit a customer for the passed transaction
     *
     * @access public
     * @param $customerId
     * @param $transactionAttribs
     * @return object Result\Successful or Result\Error
     */
    public static function credit($customerId, $transactionAttribs)
    {
        self::_validateId($customerId);
        return Transaction::credit(
                array_merge($transactionAttribs,
                        array('customerId' => $customerId)
                        )
                );
    }

    /**
     * credit a customer, assuming validations will pass
     *
     * returns a Transaction object on success
     *
     * @access public
     * @param $customerId
     * @param $transactionAttribs
     * @return object Transaction
     */
    public static function creditNoValidate($customerId, $transactionAttribs)
    {
        $result = self::credit($customerId, $transactionAttribs);
        return self::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * delete a customer by id
     *
     * @param string $customerId
     * @return Result\Successful
     */
    public static function delete($customerId)
    {
        self::_validateId($customerId);
        Http::delete('/customers/' . $customerId);
        return new Result\Successful();
    }

    /**
     * create a new sale for a customer
     *
     * @param string $customerId
     * @param array $transactionAttribs
     * @return object Result\Successful or Result\Error
     * @see Transaction::sale()
     */
    public static function sale($customerId, $transactionAttribs)
    {
        self::_validateId($customerId);
        return Transaction::sale(
                array_merge($transactionAttribs,
                        array('customerId' => $customerId)
                        )
                );
    }

    /**
     * create a new sale for a customer, assuming validations will pass
     *
     * returns a Transaction object on success
     * @access public
     * @param string $customerId
     * @param array $transactionAttribs
     * @return object Transaction
     * @throws Exception\ValidationsFailed
     * @see Transaction::sale()
     */
    public static function saleNoValidate($customerId, $transactionAttribs)
    {
        $result = self::sale($customerId, $transactionAttribs);
        return self::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * Returns a ResourceCollection of customers matching the search query.
     *
     * If <b>query</b> is a string, the search will be a basic search.
     * If <b>query</b> is a hash, the search will be an advanced search.
     * For more detailed information and examples, see {@link http://www.braintreepayments.com/gateway/customer-api#searching http://www.braintreepaymentsolutions.com/gateway/customer-api}
     *
     * @param IsNode[] $query search query
     * @return object ResourceCollection
     */
    public static function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $response = Http::post('/customers/advanced_search_ids', array('search' => $criteria));
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetch',
            'methodArgs' => array($query)
            );

        return new ResourceCollection($response, $pager);
    }

    /**
     * updates the customer record
     *
     * if calling this method in static context, customerId
     * is the 2nd attribute. customerId is not sent in object context.
     *
     * @access public
     * @param array $attributes
     * @param string $customerId (optional)
     * @return object Result\Successful or Result\Error
     */
    public static function update($customerId, $attributes)
    {
        Util::verifyKeys(self::updateSignature(), $attributes);
        self::_validateId($customerId);
        return self::_doUpdate(
            'put',
            '/customers/' . $customerId,
            array('customer' => $attributes)
        );
    }

    /**
     * update a customer record, assuming validations will pass
     *
     * if calling this method in static context, customerId
     * is the 2nd attribute. customerId is not sent in object context.
     * returns a Customer object on success
     *
     * @access public
     * @param array $attributes
     * @param string $customerId
     * @return object Customer
     * @throws Exception\ValidationsFailed
     */
    public static function updateNoValidate($customerId, $attributes)
    {
        $result = self::update($customerId, $attributes);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public static function updateCustomerUrl()
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::url", E_USER_NOTICE);
        return Configuration::merchantUrl() .
                '/customers/all/update_via_transparent_redirect_request';
    }

    /**
     * update a customer from a TransparentRedirect operation
     *
     * @access public
     * @param $queryString
     * @internal param array $attribs
     * @return object
     */
    public static function updateFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = TransparentRedirect::parseAndValidateQueryString(
                $queryString
        );
        return self::_doUpdate(
                'post',
                '/customers/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $customerAttribs array of customer data
     * @return void
     */
    protected function _initialize($customerAttribs)
    {
        // set the attributes
        $this->_attributes = $customerAttribs;

        // map each address into its own object
        $addressArray = array();
        if (isset($customerAttribs['addresses'])) {

            foreach ($customerAttribs['addresses'] AS $address) {
                $addressArray[] = Address::factory($address);
            }
        }
        $this->_set('addresses', $addressArray);

        // map each creditcard into its own object
        $ccArray = array();
        if (isset($customerAttribs['creditCards'])) {
            foreach ($customerAttribs['creditCards'] AS $creditCard) {
                $ccArray[] = CreditCard::factory($creditCard);
            }
        }
        $this->_set('creditCards', $ccArray);

    }

    /**
     * returns a string representation of the customer
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Customer,
     * or is a Customer with a different id
     *
     * @param object $otherCust customer to compare against
     * @return boolean
     */
    public function isEqual($otherCust)
    {
        return !($otherCust instanceof Customer) ? false : $this->id === $otherCust->id;
    }

    /* private class properties  */

    /**
     * @access protected
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
     * sends the create request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function _doCreate($url, $params)
    {
        $response = Http::post($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    /**
     * verifies that a valid customer id is being used
     * @ignore
     * @param string customer id
     * @throws \InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new \InvalidArgumentException(
                   'expected customer id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new \InvalidArgumentException(
                    $id . ' is an invalid customer id.'
                    );
        }
    }


    /* private class methods */

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param $httpVerb
     * @param string $url
     * @param array $params
     * @return mixed
     */
    private static function _doUpdate($httpVerb, $url, $params)
    {
        $response = Http::$httpVerb($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Customer object and encapsulates
     * it inside a Result\Successful object, or
     * encapsulates a Errors object inside a Result\Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result\Successful or Result\Error
     * @throws Exception\Unexpected
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['customer'])) {
            // return a populated instance of Customer
            return new Result\Successful(
                    self::factory($response['customer'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
            "Expected customer or apiErrorResponse"
            );
        }
    }

    /**
     *  factory method: returns an instance of Customer
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @param $attributes
     * @return object instance of Customer
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}
