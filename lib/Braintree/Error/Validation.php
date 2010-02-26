<?php
/**
 * error object returned as part of a validation error collection
 *
 * @package    Braintree
 * @subpackage Error
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * error object returned as part of a validation error collection
 * provides read-only access to $attribute, $code, and $message
 *
 * @package    Braintree
 * @subpackage Error
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read string $attribute
 * @property-read string $code
 * @property-read string $message
 */
class Braintree_Error_Validation
{
   private $_attribute;
   private $_code;
   private $_message;

    /**
     *
     * @param array $attributes
     */
    public function  __construct($attributes)
    {
        $this->_initializeFromArray($attributes);
    }
    /**
     * initializes instance properties from the keys/values of an array
     * @access protected
     * @param array $attributes array of properties to set - single level
     * @return none
     */
    private function _initializeFromArray($attributes)
    {
        foreach($attributes AS $name => $value) {
            $varName = "_$name";
            $this->$varName = Braintree_Util::delimiterToCamelCase($value, '_');
        }
    }

    /**
     *
     * @ignore
     */
    public function  __get($name)
    {
        $varName = "_$name";
        return isset($this->$varName) ? $this->$varName : null;
    }
}
