<?php
namespace Braintree;

/**
 * Braintree LocalPaymentReversed module
 *
 * @package    Braintree
 * @category   Resources
 */

/**
 * Manages Braintree LocalPaymentReversed 
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 *
 * @property-read string $paymentId
 */
class LocalPaymentReversed extends Base
{
    /**
     *  factory method: returns an instance of LocalPaymentReversed
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return LocalPaymentReversed 
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
     * @param array $LocalPaymentReversedAttribs array of localPaymentReversed data
     * @return void
     */
    protected function _initialize($localPaymentReversedAttribs)
    {
        // set the attributes
        $this->_attributes = $localPaymentReversedAttribs;
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }
}
