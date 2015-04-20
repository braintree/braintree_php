<?php

namespace Braintree\Result;

use Braintree\Instance;
use Braintree\Util;

/**
 * Braintree Successful Result.
 *
 * A Successful Result will be returned from gateway methods when
 * validations pass. It will provide access to the created resource.
 *
 * For example, when creating a customer, Result_Successful will
 * respond to <b>customer</b> like so:
 *
 * <code>
 * $result = Customer::create(array('first_name' => "John"));
 * if ($result->success) {
 *     // Result_Successful
 *     echo "Created customer {$result->customer->id}";
 * } else {
 *     // Result_Error
 * }
 * </code>
 *
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Successful extends Instance
{
    /**
     * @var bool always true
     */
    public $success = true;
    /**
     * @var string stores the internal name of the object providing access to
     */
    private $_returnObjectName;

    /**
     * @ignore
     *
     * @param string $classToReturn name of class to instantiate
     */
    public function __construct($objToReturn = null, $propertyName = null)
    {
        $this->_attributes = array();

        if (!empty($objToReturn)) {
            if (empty($propertyName)) {
                $propertyName = Util::cleanClassName(
                    get_class($objToReturn)
                );
            }

            // save the name for indirect access
            $this->_returnObjectName = $propertyName;

            // create the property!
            $this->$propertyName = $objToReturn;
        }
    }

   /**
    * @ignore
    *
    * @return string string representation of the object's structure
    */
   public function __toString()
   {
       $returnObject = $this->_returnObjectName;

       return __CLASS__.'['.$this->$returnObject->__toString().']';
   }
}
