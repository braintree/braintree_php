<?php

namespace Braintree;

/**
 * Braintree base class and initialization
 *
 *  PHP version 5
 *
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Braintree PHP Library
 *
 * Provides methods to child classes. This class cannot be instantiated.
 *
 * Updated by Matt Janssen to PSR-0 standards.
 * Removed PHP 4.0 deprecated functions.
 * Updated references and comments to pass general code inspections.
 *
 * @copyright  2010 Braintree Payment Solutions
 */
abstract class Braintree
{
    protected  $_attributes = array();

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
     * @ignore
     * @access public
     * @param string $name property name
     * @return mixed contents of instance properties
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        else {
            trigger_error('Undefined property on ' . get_class($this) . ': ' . $name, E_USER_NOTICE);
            return null;
        }
    }

    /**
     * used by isset() and empty()
     * @access public
     * @param string $name property name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    public function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     *
     * @param string $className
     * @param object $resultObj
     * @return object returns the passed object if successful
     * @throws Exception\ValidationsFailed
     */
    public static function returnObjectOrThrowException($className, $resultObj)
    {
        $resultObjName = Util::cleanClassName($className);
        if ($resultObj->success) {
            return $resultObj->$resultObjName;
        } else {
            throw new Exception\ValidationsFailed();
        }
    }
}