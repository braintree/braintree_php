<?php
namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree AddressGateway module
 * PHP Version 5
 * Creates and manages Braintree Addresses.
 *
 * An Address belongs to a Customer. It can be associated to a
 * CreditCard as the billing address. It can also be used
 * as the shipping address when creating a Transaction.
 *
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class AddressGateway
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

    /* public class methods */
    /**
     * @param array $attribs
     *
     * @return object Result, either Successful or Error
     */
    public function create($attribs)
    {
        Util::verifyKeys(self::createSignature(), $attribs);
        $customerId = isset($attribs['customerId']) ?
            $attribs['customerId'] :
            null;

        $this->_validateCustomerId($customerId);
        unset($attribs['customerId']);

        return $this->_doCreate(
            '/customers/'.$customerId.'/addresses',
            array('address' => $attribs)
        );
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Address object instead of a Result.
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
     * delete an address by id.
     *
     * @param mixed  $customerOrId
     * @param string $addressId
     */
    public function delete($customerOrId = null, $addressId = null)
    {
        $this->_validateId($addressId);
        $customerId = $this->_determineCustomerId($customerOrId);
        $path = $this->_config->merchantPath().'/customers/'.$customerId.'/addresses/'.$addressId;
        $this->_http->delete($path);

        return new Result\Successful();
    }

    /**
     * find an address by id.
     *
     * Finds the address with the given <b>addressId</b> that is associated
     * to the given <b>customerOrId</b>.
     * If the address cannot be found, a NotFound exception will be thrown.
     *
     *
     * @param mixed  $customerOrId
     * @param string $addressId
     *
     * @return object Address
     *
     * @throws Exception\NotFound
     */
    public function find($customerOrId, $addressId)
    {
        $customerId = $this->_determineCustomerId($customerOrId);
        $this->_validateId($addressId);

        try {
            $path = $this->_config->merchantPath().'/customers/'.$customerId.'/addresses/'.$addressId;
            $response = $this->_http->get($path);

            return Address::factory($response['address']);
        } catch (Exception\NotFound $e) {
            throw new Exception\NotFound(
            'address for customer '.$customerId.
                ' with id '.$addressId.' not found.'
            );
        }
    }

    /**
     * updates the address record.
     *
     * if calling this method in context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     *
     * @param array  $attributes
     * @param mixed  $customerOrId (only used in call)
     * @param string $addressId    (only used in call)
     *
     * @return object Result\Successful or Result\Error
     */
    public function update($customerOrId, $addressId, $attributes)
    {
        $this->_validateId($addressId);
        $customerId = $this->_determineCustomerId($customerOrId);
        Util::verifyKeys(self::updateSignature(), $attributes);

        $path = $this->_config->merchantPath().'/customers/'.$customerId.'/addresses/'.$addressId;
        $response = $this->_http->put($path, array('address' => $attributes));

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * update an address record, assuming validations will pass.
     *
     * if calling this method in context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     * @param array  $transactionAttribs
     * @param string $customerId
     *
     * @return object Transaction
     *
     * @throws Exception\ValidationsFailed
     *
     * @see Address::update()
     */
    public function updateNoValidate($customerOrId, $addressId, $attributes)
    {
        $result = $this->update($customerOrId, $addressId, $attributes);

        return Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * creates a full array signature of a valid create request.
     *
     * @return array gateway create request format
     */
    public static function createSignature()
    {
        return array(
            'company', 'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
            'countryName', 'customerId', 'extendedAddress', 'firstName',
            'lastName', 'locality', 'postalCode', 'region', 'streetAddress',
        );
    }

    /**
     * creates a full array signature of a valid update request.
     *
     * @return array gateway update request format
     */
    public static function updateSignature()
    {
        // TODO: remove customerId from update signature
        return self::createSignature();
    }

    /**
     * verifies that a valid address id is being used.
     *
     * @ignore
     *
     * @param string $id address id
     *
     * @throws InvalidArgumentException
     */
    private function _validateId($id = null)
    {
        if (empty($id) || trim($id) == '') {
            throw new InvalidArgumentException(
            'expected address id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
            $id.' is an invalid address id.'
            );
        }
    }

    /**
     * verifies that a valid customer id is being used.
     *
     * @ignore
     *
     * @param string $id customer id
     *
     * @throws InvalidArgumentException
     */
    private function _validateCustomerId($id = null)
    {
        if (empty($id) || trim($id) == '') {
            throw new InvalidArgumentException(
            'expected customer id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
            $id.' is an invalid customer id.'
            );
        }
    }

    /**
     * determines if a string id or Customer object was passed.
     *
     * @ignore
     *
     * @param mixed $customerOrId
     *
     * @return string customerId
     */
    private function _determineCustomerId($customerOrId)
    {
        $customerId = ($customerOrId instanceof Customer) ? $customerOrId->id : $customerOrId;
        $this->_validateCustomerId($customerId);

        return $customerId;
    }

    /* private class methods */
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
    private function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath().$subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses.
     *
     * creates a new Address object and encapsulates
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
        if (isset($response['address'])) {
            // return a populated instance of Address
            return new Result\Successful(
                Address::factory($response['address'])
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
