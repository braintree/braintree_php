<?php
namespace Braintree;

/**
 * Braintree PHP Library.
 *
 * Braintree base class and initialization
 * Provides methods to child classes. This class cannot be instantiated.
 *
 *  PHP version 5
 */
abstract class Base
{
    protected $_attributes = [];

    /**
     * @ignore
     * don't permit an explicit call of the constructor!
     * (like $t = new Transaction())
     */
    protected function __construct()
    {
    }

    /**
     * Disable cloning of objects
     *
     * @ignore
     */
    protected function __clone()
    {
    }

    /**
     * Accessor for instance properties stored in the private $_attributes property
     *
     * @ignore
     * @param string $name
     * @return mixed
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
     * Checks for the existance of a property stored in the private $_attributes property
     *
     * @ignore
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * Mutator for instance properties stored in the private $_attributes property
     *
     * @ignore
     * @param string $key
     * @param mixed $value
     */
    public function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

	/**
	 * This static method is called for classes exported by var_export() since PHP 5.1.0.
	 *
	 * @param $properties array containing exported properties in the form array('property' => value, ...)
	 *
	 * @return created class
	 */
	static public function __set_state( $properties )
	{
		$c = get_called_class();

		$class = new $c();

		while ( ( list( $k, $v ) = each( $properties ) ) != false ) {
			$class->$k = $v;
		}

		return $class;
	}
}
