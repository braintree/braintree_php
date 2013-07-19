<?php

namespace Braintree\Exception;

/**
 * Raised when an unexpected error occurs
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2010 Braintree Payment Solutions
 */
use Braintree\Exception;

/**
 * Raised when an error occurs that the client library is not built to handle.
 * This shouldn't happen.
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2010 Braintree Payment Solutions
 */
class Unexpected extends Exception
{

}
