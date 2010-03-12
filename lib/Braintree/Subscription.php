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
class Braintree_Subscription extends Braintree
{
    private $_attributes = array();

    /* public class methods */
    /**
     *
     * @access public
     * @param  array  $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attributes)
    {
        $response = Braintree_Http::post('/subscriptions', array('subscription' => $attributes));

        return self::_verifyGatewayResponse($response);
        // Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        // $customerId = isset($attribs['customerId']) ?
        //     $attribs['customerId'] :
        //     null;

        // self::_validateCustomerId($customerId);
        // unset($attribs['customerId']);
        // return self::_doCreate(
        //     '/customers/' . $customerId . '/addresses',
        //     array('address' => $attribs)
        // );
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        else {
            return parent::__get($name);
        }
    }

    protected function _initialize($attributes)
    {
        $this->_attributes = array_merge($this->_attributes, $attributes);

        $transactionArray = null;
        if (isset($attributes['transactions'])) {
            foreach ($attributes['transactions'] AS $transaction) {
                $transactionArray[] = Braintree_Transaction::factory($transaction);
            }
        }
        $this->_attributes['transactions'] = $transactionArray;
    }

    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['subscription'])) {
            return new Braintree_Result_Successful(
                self::factory($response['subscription'])
            );
        } else if (isset($response['apiErrorResponse'])) {
//            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
//            throw new Braintree_Exception_Unexpected('Expected subscription or apiErrorResponse');
        }
    }
}
?>
