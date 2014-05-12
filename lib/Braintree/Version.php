<?php
/**
 * Braintree Library Version
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * stores version information about the Braintree library
 *
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
final class Braintree_Version
{
    /**
     * class constants
     */
    const MAJOR = 2;
    const MINOR = 27;
    const TINY = 2;

    /**
     * @ignore
     * @access protected
     */
    protected function  __construct()
    {
    }

    /**
     *
     * @return string the current library version
     */
    public static function get()
    {
        return self::MAJOR.'.'.self::MINOR.'.'.self::TINY;
    }
}
