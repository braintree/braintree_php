<?php

namespace Braintree;

/**
 * Braintree Library Version
 * stores version information about the Braintree library
 */
class Version
{
    const MAJOR = 6;
    const MINOR = 11;
    const TINY = 2;

    protected function __construct()
    {
    }

    /**
     * Get the version
     *
     * @return string the current library version
     */
    public static function get()
    {
        return self::MAJOR . '.' . self::MINOR . '.' . self::TINY;
    }
}
