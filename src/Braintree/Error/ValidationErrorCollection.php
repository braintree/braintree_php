<?php namespace Braintree\Error;

use Braintree\Collection;

/**
 * collection of errors enumerating all validation errors for a given request
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Validation errors, see {@link http://www.braintreepayments.com/gateway/validation-errors http://www.braintreepaymentsolutions.com/gateway/validation-errors}
 *
 * @package    Braintree
 * @subpackage Error
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read array $errors
 * @property-read array $nested
 */
class ValidationErrorCollection extends Collection
{
    private $_errors = array();
    private $_nested = array();

    /**
     * @ignore
     */
    public function __construct($data)
    {
        foreach ($data as $key => $errorData) {
            // map errors to new collections recursively
            if ($key == 'errors') {
                foreach ($errorData as $error) {
                    $this->_errors[] = new Validation($error);
                }
            } else {
                $this->_nested[$key] = new ValidationErrorCollection($errorData);
            }
        }
    }

    public function deepAll()
    {
        $validationErrors = array_merge(array(), $this->_errors);
        foreach ($this->_nested as $nestedErrors) {
            $validationErrors = array_merge($validationErrors, $nestedErrors->deepAll());
        }
        return $validationErrors;
    }

    public function deepSize()
    {
        $total = sizeof($this->_errors);
        foreach ($this->_nested as $_nestedErrors) {
            $total = $total + $_nestedErrors->deepSize();
        }
        return $total;
    }

    public function forIndex($index)
    {
        return $this->forKey("index" . $index);
    }

    public function forKey($key)
    {
        return isset($this->_nested[$key]) ? $this->_nested[$key] : null;
    }

    public function onAttribute($attribute)
    {
        $matches = array();
        foreach ($this->_errors as $key => $error) {
            if ($error->attribute == $attribute) {
                $matches[] = $error;
            }
        }
        return $matches;
    }


    public function shallowAll()
    {
        return $this->_errors;
    }

    /**
     *
     * @ignore
     */
    public function __get($name)
    {
        $varName = "_$name";
        return isset($this->$varName) ? $this->$varName : null;
    }

    /**
     * @ignore
     */
    public function __toString()
    {
        $output = array();

        // TODO: implement scope
        if (!empty($this->_errors)) {
            $output[] = $this->_inspect($this->_errors);
        }
        if (!empty($this->_nested)) {
            foreach ($this->_nested as $key => $values) {
                $output[] = $this->_inspect($this->_nested);
            }
        }
        return join(', ', $output);
    }

    /**
     * @ignore
     */
    private function _inspect($errors, $scope = null)
    {
        $eOutput = '[' . __CLASS__ . '/errors:[';
        foreach ($errors as $error => $errorObj) {
            $outputErrs[] = "({$errorObj->error['code']} {$errorObj->error['message']})";
        }
        $eOutput .= join(', ', $outputErrs) . ']]';

        return $eOutput;
    }
}
