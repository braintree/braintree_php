<?php
namespace Braintree;

use Exception;

/**
 * Braintree PHP Library.
 *
 * Braintree base class and initialization
 * Provides methods to child classes. This class cannot be instantiated.
 *
 *  PHP version 5
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
abstract class Base
{
    protected $_attributes = array();

    /**
     * @ignore
     * don't permit an explicit call of the constructor!
     * (like $t = new Transaction())
     */
    protected function __construct()
    {
    }
    /**
     * @ignore
     *  don't permit cloning the instances (like $x = clone $v)
     */
    protected function __clone()
    {
    }

    /**
     * returns private/nonexistent instance properties
     *
     * @ignore
     *
     * @param string $name property name
     *
     * @return mixed contents of instance properties
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } else {
            trigger_error('Undefined property on ' . get_class($this) . ': ' . $name, E_USER_NOTICE);

            return;
        }
    }

    /**
     * used by isset() and empty()
     *
     * @param string $name property name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    public function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    public static function check()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            throw new Exception('PHP version >= 5.4.0 required');
        }

        $requiredExtensions = array('xmlwriter', 'openssl', 'dom', 'hash', 'curl');

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new Exception('The Braintree library requires the ' . $ext . ' extension.');
            }
        }

        return 'Ok';
    }
}
