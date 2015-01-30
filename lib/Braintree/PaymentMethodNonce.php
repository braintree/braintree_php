<?php
/**
 * Braintree PaymentMethodNonce module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethodNonces
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class Braintree_PaymentMethodNonce extends Braintree
{
    // static methods redirecting to gateway

    public static function create($token)
    {
        return Braintree_Configuration::gateway()->paymentMethodNonce()->create($token);
    }

    public static function factory($attributes)
    {
        $defaultAttributes = array(
            'nonce' => '',
        );

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }

    protected function _initialize($nonceAttributes)
    {
        $this->_attributes = $nonceAttributes;
        $this->_set('nonce', $nonceAttributes['nonce']);
    }
}
