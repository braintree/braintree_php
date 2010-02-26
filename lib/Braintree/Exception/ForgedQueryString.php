<?php
/**
 * Raised when a suspected forged query string is present
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Raised from methods that confirm transparent request requests
 * when the given query string cannot be verified. This may indicate
 * an attempted hack on the merchant's transparent redirect
 * confirmation URL.
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_Exception_ForgedQueryString extends Braintree_Exception
{

}
