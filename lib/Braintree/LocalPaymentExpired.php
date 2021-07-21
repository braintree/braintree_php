<?php

namespace Braintree;

/**
 * Braintree LocalPaymentExpired module
 *
 * @package    Braintree
 * @category   Resources
 */

/**
 * Manages Braintree LocalPaymentExpired
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 *
 * @property-read string $paymentId
 * @property-read string $paymentContextId
 */
class LocalPaymentExpired extends Base
{
    /**
     *  factory method: returns an instance of LocalPaymentExpired
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return LocalPaymentExpired
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $LocalPaymentExpiredAttribs array of localPaymentExpired data
     * @return void
     */
    protected function _initialize($localPaymentExpiredAttribs)
    {
        // set the attributes
        $this->_attributes = $localPaymentExpiredAttribs;
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }
}
