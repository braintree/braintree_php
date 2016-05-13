<?php
namespace Braintree\Exception;

use Braintree\Exception;

/**
 * Raised when the gateway request rate-limit is exceeded.
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 */
class TooManyRequests extends Exception
{

}
class_alias('Braintree\Exception\TooManyRequests', 'Braintree_Exception_TooManyRequests');
