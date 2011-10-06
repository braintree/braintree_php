<?php
/**
 * Braintree Address module
 *
 * PHP Version 5
 *
 * @package   Braintree
 * @copyright 2010 Braintree Payment Solutions
 */
/**
 * Creates and manages Braintree Addresses
 *
 * An Address belongs to a Customer. It can be associated to a
 * CreditCard as the billing address. It can also be used
 * as the shipping address when creating a Transaction.
 *
 * @package   Braintree
 * @copyright 2010 Braintree Payment Solutions
 *
 * @property-read string $company
 * @property-read string $countryName
 * @property-read string $createdAt
 * @property-read string $customerId
 * @property-read string $extendedAddress
 * @property-read string $firstName
 * @property-read string $id
 * @property-read string $lastName
 * @property-read string $locality
 * @property-read string $postalCode
 * @property-read string $region
 * @property-read string $streetAddress
 * @property-read string $updatedAt
 */
class Braintree_Address extends Braintree
{

    /* public class methods */
    /**
     *
     * @access public
     * @param  array  $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        $customerId = isset($attribs['customerId']) ? 
            $attribs['customerId'] : 
            null;

        self::_validateCustomerId($customerId);
        unset($attribs['customerId']);
        return self::_doCreate(
            '/customers/' . $customerId . '/addresses',
            array('address' => $attribs)
        );
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Braintree_Address object instead of a Result
     *
     * @access public
     * @param  array $attribs
     * @return object
     * @throws Braintree_Exception_ValidationError
     */
    public static function createNoValidate($attribs)
    {
        $result = self::create($attribs);
        return self::returnObjectOrThrowException(__CLASS__, $result);

    }

    /**
     * delete an address by id
     *
     * @param mixed $customerOrId
     * @param string $addressId
     */
    public static function delete($customerOrId = null, $addressId = null)
    {
        self::_validateId($addressId);
        $customerId = self::_determineCustomerId($customerOrId);
        Braintree_Http::delete(
            '/customers/' . $customerId . '/addresses/' . $addressId
        );
        return new Braintree_Result_Successful();
    }

    /**
     * find an address by id
     *
     * Finds the address with the given <b>addressId</b> that is associated
     * to the given <b>customerOrId</b>.
     * If the address cannot be found, a NotFound exception will be thrown.
     *
     *
     * @access public
     * @param mixed $customerOrId
     * @param string $addressId
     * @return object Braintree_Address
     * @throws Braintree_Exception_NotFound
     */
    public static function find($customerOrId, $addressId)
    {

        $customerId = self::_determineCustomerId($customerOrId);
        self::_validateId($addressId);

        try {
            $response = Braintree_Http::get(
                '/customers/' . $customerId . '/addresses/' . $addressId
            );
            return self::factory($response['address']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
            'address for customer ' . $customerId .
                ' with id ' . $addressId . ' not found.'
            );
        }

    }

    /**
     * returns false if comparing object is not a Braintree_Address,
     * or is a Braintree_Address with a different id
     *
     * @param object $other address to compare against
     * @return boolean
     */
    public function isEqual($other)
    {
        return !($other instanceof Braintree_Address) ?
            false :
            ($this->id === $other->id && $this->customerId === $other->customerId);
    }

    /**
     * updates the address record
     *
     * if calling this method in static context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     *
     * @access public
     * @param array $attributes
     * @param mixed $customerOrId (only used in static call)
     * @param string $addressId (only used in static call)
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public static function update($customerOrId, $addressId, $attributes)
    {
        self::_validateId($addressId);
        $customerId = self::_determineCustomerId($customerOrId);
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);

        $response = Braintree_Http::put(
            '/customers/' . $customerId . '/addresses/' . $addressId,
            array('address' => $attributes)
        );

        return self::_verifyGatewayResponse($response);

    }

    /**
     * update an address record, assuming validations will pass
     *
     * if calling this method in static context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     * @access public
     * @param array $transactionAttribs
     * @param string $customerId
     * @return object Braintree_Transaction
     * @throws Braintree_Exception_ValidationsFailed
     * @see Braintree_Address::update()
     */
    public static function updateNoValidate($customerOrId, $addressId, $attributes)
    {
        $result = self::update($customerOrId, $addressId, $attributes);
        return self::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * creates a full array signature of a valid create request
     * @return array gateway create request format
     */
    public static function createSignature()
    {
        return array(
            'company', 'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
            'countryName', 'customerId', 'extendedAddress', 'firstName',
            'lastName', 'locality', 'postalCode', 'region', 'streetAddress'
        );
    }

    /**
     * creates a full array signature of a valid update request
     * @return array gateway update request format
     */
    public static function updateSignature()
    {
        // TODO: remove customerId from update signature
        return self::createSignature();

    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @ignore
     * @return var
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $addressAttribs array of address data
     * @return none
     */
    protected function _initialize($addressAttribs)
    {
        // set the attributes
        $this->_attributes = $addressAttribs;
    }

    /**
     * verifies that a valid address id is being used
     * @ignore
     * @param string $id address id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null)
    {
        if (empty($id) || trim($id) == "") {
            throw new InvalidArgumentException(
            'expected address id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
            $id . ' is an invalid address id.'
            );
        }
    }

    /**
     * verifies that a valid customer id is being used
     * @ignore
     * @param string $id customer id
     * @throws InvalidArgumentException
     */
    private static function _validateCustomerId($id = null)
    {
        if (empty($id) || trim($id) == "") {
            throw new InvalidArgumentException(
            'expected customer id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
            $id . ' is an invalid customer id.'
            );
        }

    }

    /**
     * determines if a string id or Customer object was passed
     * @ignore
     * @param mixed $customerOrId
     * @return string customerId
     */
    private static function _determineCustomerId($customerOrId)
    {
        $customerId = ($customerOrId instanceof Braintree_Customer) ? $customerOrId->id : $customerOrId;
        self::_validateCustomerId($customerId);
        return $customerId;

    }

    /* private class methods */
    /**
     * sends the create request to the gateway
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    private static function _doCreate($url, $params)
    {
        $response = Braintree_Http::post($url, $params);

        return self::_verifyGatewayResponse($response);

    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Braintree_Address object and encapsulates
     * it inside a Braintree_Result_Successful object, or
     * encapsulates a Braintree_Errors object inside a Result_Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result_Successful or Result_Error
     * @throws Braintree_Exception_Unexpected
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['address'])) {
            // return a populated instance of Braintree_Address
            return new Braintree_Result_Successful(
                self::factory($response['address'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected address or apiErrorResponse"
            );
        }

    }

    /**
     *  factory method: returns an instance of Braintree_Address
     *  to the requesting method, with populated properties
     * @ignore
     * @return object instance of Braintree_Address
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;

    }
}
