<?php
/**
 * Braintree Error Result
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Braintree Error Result
 *
 * An Error Result will be returned from gateway methods when
 * the gateway responds with an error. It will provide access
 * to the original request.
 * For example, when voiding a transaction, Error Result will
 * respond to the void request if it failed:
 *
 * <code>
 * <?php
 * $result = Braintree_Transaction::void('abc123');
 * if ($result->success) {
 *     // Successful Result
 * } else {
 *     // Braintree_Result_Error
 * }
 * ?>
 * </code>
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read array $params original passed params
 * @property-read object $errors Braintree_Error_ErrorCollection
 * @property-read object $creditCardVerification credit card verification data
 */
class Braintree_Result_Error
{
   private $_params;
   private $_errors;
   private $_creditCardVerification;
   /**
    *
    * @var boolean always false
    */
   public $success = false;

   /**
    * overrides default constructor
    * @param array $response gateway response array
    */
   public function  __construct($response)
   {
       $this->_params = $response['params'];
       $this->_errors = new Braintree_Error_ErrorCollection($response['errors']);
       // create a CreditCardVerification object
       // populated with gateway response data
       if(isset($response['verification'])) {
           $this->_creditCardVerification =
                   new Braintree_Result_CreditCardVerification(
                           $response['verification']
                           );
       } else {
           unset($this->_creditCardVerification);
       }
   }

   /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return var
     */
    public function  __toString()
    {
        $output = Braintree_Util::implodeAssociativeArray($this->_params);
        $output .= sprintf('%s', $this->_errors);
        if (isset($this->_creditCardVerification)) {
            $output .= sprintf('%s', $this->_creditCardVerification);
        }
        return __CLASS__ .'['.$output.']';
    }

    /**
     * @ignore
     */
    public function __get($name)
    {
        $key = "_$name";
        if (isset($key)) {
            return $this->$key;
        }
        return null;
    }
}
