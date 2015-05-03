<?php
namespace Braintree;

/**
 * Braintree PaymentMethod module.
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethods.
 *
 * <b>== More information ==</b>
 *
 *
 * @category   Resources
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class PaymentMethod extends Braintree
{
    // static methods redirecting to gateway

    public static function create($attribs)
    {
        return Configuration::gateway()->paymentMethod()->create($attribs);
    }

    public static function find($token)
    {
        return Configuration::gateway()->paymentMethod()->find($token);
    }

    public static function update($token, $attribs)
    {
        return Configuration::gateway()->paymentMethod()->update($token, $attribs);
    }

    public static function delete($token)
    {
        return Configuration::gateway()->paymentMethod()->delete($token);
    }
}
