<?php
/**
 * Braintree Successful Result
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Braintree Successful Result
 *
 * A Successful Result will be returned from gateway methods when
 * validations pass. It will provide access to the created resource.
 *
 * For example, when creating a customer, Braintree_Result_Successful will
 * respond to <b>customer</b> like so:
 *
 * <code>
 * $result = Braintree_Customer::create(array('first_name' => "John"));
 * if ($result->success) {
 *     // Braintree_Result_Successful
 *     echo "Created customer {$result->customer->id}";
 * } else {
 *     // Braintree_Result_Error
 * }
 * </code>
 *
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_Result_Successful extends Braintree_Instance
{
    /**
     *
     * @var boolean always true
     */
    public $success = true;
    /**
     *
     * @var string stores the internal name of the object providing access to
     */
    private $_returnObjectName;

    /**
     * @ignore
     * @param string $classToReturn name of class to instantiate
     */
    public function __construct($objToReturn = null)
    {
        if(!empty($objToReturn)) {
            // get a lowercase direct name for the property
            $property = Braintree_Util::cleanClassName(
                    get_class($objToReturn)
                    );
            // save the name for indirect access
            $this->_returnObjectName = $property;

            // create the property!
            $this->$property = $objToReturn;
        }
    }


   /**
    *
    * @ignore
    * @return string string representation of the object's structure
    */
   public function __toString()
   {
       $returnObject = $this->_returnObjectName;
       return __CLASS__ . '['.$this->$returnObject->__toString().']';
   }

}
