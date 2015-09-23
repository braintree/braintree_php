<?php
namespace Braintree;

/**
 * Braintree Library Version
 * stores version information about the Braintree library.
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
final class Version
{
    /**
     * class constants.
     */
    const MAJOR = 3;
    const MINOR = 4;
    const TINY  = 0;

    /**
     * @ignore
     */
    protected function __construct()
    {
    }

    /**
     * @return string the current library version
     */
    public static function get()
    {
        return self::MAJOR.'.'.self::MINOR.'.'.self::TINY;
    }
}
