<?php
/**
 * Braintree Library Version
 *
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * stores version information about the Braintree library
 *
 *
 * @copyright  2010 Braintree Payment Solutions
 */
final class Braintree_Version
{
    /**
     * class constants
     */
    const MAJOR = 2;
    const MINOR = 21;
    const TINY = 0;

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
