<?php
namespace Braintree;

/**
 * Braintree UnknownPaymentMethod module.
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree UnknownPaymentMethod.
 *
 * <b>== More information ==</b>
 *
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $imageUrl
 */
class UnknownPaymentMethod extends Braintree
{
    /**
     *  factory method: returns an instance of UnknownPaymentMethod
     *  to the requesting method, with populated properties.
     *
     * @ignore
     *
     * @return object instance of UnknownPaymentMethod
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $values = array_values($attributes);
        $instance->_initialize(array_shift($values));

        return $instance;
    }

    /* instance methods */

    /**
     * returns false if default is null or false.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * sets instance properties from an array of values.
     *
     * @param array $unknownPaymentMethodAttribs array of unknownPaymentMethod data
     *
     * @return none
     */
    protected function _initialize($unknownPaymentMethodAttribs)
    {
        // set the attributes
        $this->imageUrl = 'https://assets.braintreegateway.com/payment_method_logo/unknown.png';
        $this->_attributes = $unknownPaymentMethodAttribs;
    }
}
