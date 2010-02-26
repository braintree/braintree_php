<?php
/**
 *
 * Error handler
 *
 * @package    Braintree
 * @subpackage Errors
 * @category   Errors
 * @copyright  2010 Braintree Payment Solutions
 */


/**
 * Handles validation errors
 *
 * Contains a read-only property $error which is a ValidationErrorCollection
 *
 * @package    Braintree
 * @subpackage Errors
 * @category   Errors
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read object $errors
 */
class Braintree_Error_ErrorCollection
{
    private $_errors;

    public function __construct($errorData)
    {
        $this->_errors =
                new Braintree_Error_ValidationErrorCollection($errorData);
    }

    /**
     * Returns the total number of validation errors at all levels of nesting. For example,
     *if creating a customer with a credit card and a billing address, and each of the customer,
     * credit card, and billing address has 1 error, this method will return 3.
     *
     * @return int size
     */
    public function deepSize()
    {
        $size = $this->_errors->deepSize();
        return $size;
    }

    /**
     * return errors for the passed key name
     *
     * @param string $key
     * @return mixed
     */
    public function forKey($key)
    {
        return $this->_errors->forKey($key);
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

    public function  __toString()
    {
        return sprintf('%s', $this->_errors);
    }
}
