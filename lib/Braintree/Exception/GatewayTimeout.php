<?php
namespace Braintree\Exception;

use Braintree\Exception;

/**
 * Raised when a gateway response timeout occurs.
 *
 * @package    Braintree
 * @subpackage Exception
 */
class GatewayTimeout extends Exception
{

}
class_alias('Braintree\Exception\GatewayTimeout', 'Braintree_Exception_GatewayTimeout');
