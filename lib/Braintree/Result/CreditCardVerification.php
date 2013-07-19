<?php

namespace Braintree\Result;

/**
 * Braintree Credit Card Verification Result
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2010 Braintree Payment Solutions
 */
use Braintree\Util;

/**
 * Braintree Credit Card Verification Result
 *
 * This object is returned as part of an Error Result; it provides
 * access to the credit card verification data from the gateway
 *
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read string $avsErrorResponseCode
 * @property-read string $avsPostalCodeResponseCode
 * @property-read string $avsStreetAddressResponseCode
 * @property-read string $cvvResponseCode
 * @property-read string $status
 *
 */
class CreditCardVerification
{
    // Status
    const FAILED                   = 'failed';
    const GATEWAY_REJECTED         = 'gateway_rejected';
    const PROCESSOR_DECLINED       = 'processor_declined';
    const VERIFIED                 = 'verified';

    private $_attributes;

    /**
     * @ignore
     */
    public function  __construct($attributes)
    {
        $this->_initializeFromArray($attributes);
    }
    /**
     * initializes instance properties from the keys/values of an array
     * @ignore
     * @access protected
     * @param <type> $aAttribs array of properties to set - single level
     * @return void
     */
    private function _initializeFromArray($attributes)
    {
        $this->_attributes = $attributes;
        foreach($attributes AS $name => $value) {
            $varName = "_$name";
            $this->$varName = $value;
            // $this->$varName = Util::delimiterToCamelCase($value, '_');
        }
    }
    /**
     *
     * @ignore
     */
    public function  __get($name)
    {
        $varName = "_$name";
        return isset($this->$varName) ? $this->$varName : null;
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
}
