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
   private $_summary;
   private $_creditCardVerification;
   private $_transaction;
   /**
    *
    * @var boolean always false
    */
   public $success = false;

    /**
     * return original value for a field
     * For example, if a user tried to submit 'invalid-email' in the html field transaction[customer][email],
     * $result->valueForHtmlField("transaction[customer][email]") would yield "invalid-email"
     *
     * @param string $field
     * @return string
     */
   public function valueForHtmlField($field)
   {
       $pieces = preg_split("/[\[\]]+/", $field, 0, PREG_SPLIT_NO_EMPTY);
       $params = $this->_params;
       foreach(array_slice($pieces, 0, -1) as $key) {
           $params = $params[Braintree_Util::delimiterToCamelCase($key)];
       }
       $finalKey = Braintree_Util::delimiterToCamelCase(end($pieces));
       $fieldValue = isset($params[$finalKey]) ? $params[$finalKey] : null;
       return $fieldValue;
   }

   /**
    * overrides default constructor
    * @ignore
    * @param array $response gateway response array
    */
   public function  __construct($response)
   {
       $this->_params = $response['params'];
       $this->_errors = new Braintree_Error_ErrorCollection($response['errors']);
       $this->_summary = $response['summary'];
       // create a CreditCardVerification object
       // populated with gateway response data
       if(isset($response['verification'])) {
           $this->_creditCardVerification = new Braintree_Result_CreditCardVerification($response['verification']);
       } else {
           unset($this->_creditCardVerification);
       }
       if(isset($response['transaction'])) {
           $this->_transaction = Braintree_Transaction::factory($response['transaction']);
       } else {
           unset($this->_transaction);
       }
   }

   /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @ignore
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
        if (isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }
}
