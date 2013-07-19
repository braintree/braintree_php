<?php

namespace Braintree\Exception;

/**
 * Raised when authorization fails
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2010 Braintree Payment Solutions
 */
use Braintree\Exception;


/**
 * Raised when the API key being used is not authorized to perform
 * the attempted action according to the roles assigned to the user
 * who owns the API key.
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2010 Braintree Payment Solutions
 */
class Authorization extends Exception
{

}
