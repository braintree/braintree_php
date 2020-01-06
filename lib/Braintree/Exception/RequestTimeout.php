<?php
namespace Braintree\Exception;

use Braintree\Exception;

/**
 * Raised when a client request timeout occurs.
 *
 * @package    Braintree
 * @subpackage Exception
 */
class RequestTimeout extends Exception
{

}
class_alias('Braintree\Exception\RequestTimeout', 'Braintree_Exception_RequestTimeout');
