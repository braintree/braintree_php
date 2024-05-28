<?php

namespace Braintree;

use Braintree\HttpHelpers\HttpClientAware;
use InvalidArgumentException;

/**
 * Braintree AddressGateway module
 * Creates and manages Braintree Addresses
 *
 * An Address belongs to a Customer. It can be associated to a
 * CreditCard as the billing address. It can also be used
 * as the shipping address when creating a Transaction.
 */
class AddressGateway
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
     * Create an Address
     *
     * @param array $attribs containing request parameters
     *
     * @return Result\Successful|Result\Error
     */
    public function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        $customerId = isset($attribs['customerId']) ?
            $attribs['customerId'] :
            null;

        $this->_validateCustomerId($customerId);
        unset($attribs['customerId']);
        try {
            return $this->_doCreate(
                '/customers/' . $customerId . '/addresses',
                ['address' => $attribs]
            );
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'Customer ' . $customerId . ' not found.'
            );
        }
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Address object instead of a Result
     *
     * @param array $attribs containing request parameters
     *
     * @throws Exception\ValidationError
     *
     * @return Address
     */
    public function createNoValidate($attribs)
    {
        $result = $this->create($attribs);
        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * delete an address by id
     *
     * @param mixed  $customerOrId either a customer object or string ID of customer
     * @param string $addressId    optional unique identifier
     *
     * @return Result\Successful|Result\Error
     */
    public function delete($customerOrId = null, $addressId = null)
    {
        $this->_validateId($addressId);
        $customerId = $this->_determineCustomerId($customerOrId);
        $path = $this->_config->merchantPath() . '/customers/' . $customerId . '/addresses/' . $addressId;
        $this->_http->delete($path);
        return new Result\Successful();
    }

    /**
     * find an address by id
     *
     * Finds the address with the given <b>addressId</b> that is associated
     * to the given <b>customerOrId</b>.
     * If the address cannot be found, a NotFound exception will be thrown.
     *
     * @param mixed  $customerOrId either a customer object or string ID of customer
     * @param string $addressId    optional unique identifier
     *
     * @throws Exception\NotFound
     *
     * @return Address
     */
    public function find($customerOrId, $addressId)
    {

        $customerId = $this->_determineCustomerId($customerOrId);
        $this->_validateId($addressId);

        try {
            $path = $this->_config->merchantPath() . '/customers/' . $customerId . '/addresses/' . $addressId;
            $response = $this->_http->get($path);
            return Address::factory($response['address']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
                'address for customer ' . $customerId .
                ' with id ' . $addressId . ' not found.'
            );
        }
    }

    /**
     * updates the address record
     *
     * if calling this method in context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     * @param mixed  $customerOrId (only used in call)
     * @param string $addressId    (only used in call)
     * @param array  $attributes   containing request parameters
     *
     * @return Result\Successful|Result\Error
     */
    public function update($customerOrId, $addressId, $attributes)
    {
        $this->_validateId($addressId);
        $customerId = $this->_determineCustomerId($customerOrId);
        Util::verifyKeys(self::updateSignature(), $attributes);

        $path = $this->_config->merchantPath() . '/customers/' . $customerId . '/addresses/' . $addressId;
        $response = $this->_http->put($path, ['address' => $attributes]);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * update an address record, assuming validations will pass
     *
     * if calling this method in context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     * @param mixed  $customerOrId (only used in call)
     * @param string $addressId    (only used in call)
     * @param array  $attributes   containing request parameters
     *
     * @throws Exception\ValidationsFailed
     *
     * @see Address::update()
     *
     * @return Address
     */
    public function updateNoValidate($customerOrId, $addressId, $attributes)
    {
        $result = $this->update($customerOrId, $addressId, $attributes);
        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * creates a full array signature of a valid create request
     *
     * @return array gateway create request format
     */
    public static function createSignature()
    {
        return [
            'company',
            'countryCodeAlpha2',
            'countryCodeAlpha3',
            'countryCodeNumeric',
            'countryName',
            'customerId',
            'extendedAddress',
            'firstName',
            'lastName',
            'locality',
            'phoneNumber',
            'postalCode',
            'region',
            'streetAddress'
        ];
    }

    /**
     * creates a full array signature of a valid update request
     *
     * @return array gateway update request format
     */
    public static function updateSignature()
    {
        return self::createSignature();
    }

    /**
     * verifies that a valid address id is being used
     *
     * @param string $id address id
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    private function _validateId($id = null)
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
     *
     * @param string $id customer id
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    private function _validateCustomerId($id = null)
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
     *
     * @param mixed $customerOrId either a customer object or string unique identifier
     *
     * @return string customerId
     */
    private function _determineCustomerId($customerOrId)
    {
        $customerId = ($customerOrId instanceof Customer) ? $customerOrId->id : $customerOrId;
        $this->_validateCustomerId($customerId);
        return $customerId;
    }

    private function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response['address'])) {
            // return a populated instance of Address
            return new Result\Successful(
                Address::factory($response['address'])
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
                "Expected address or apiErrorResponse"
            );
        }
    }
}
