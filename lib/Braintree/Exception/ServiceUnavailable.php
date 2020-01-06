<?php
namespace Braintree\Exception;

use Braintree\Exception;

/**
 * Raised when the gateway service is unavailable.
 *
 * @package    Braintree
 * @subpackage Exception
 */
class ServiceUnavailable extends Exception
{

}
class_alias('Braintree\Exception\ServiceUnavailable', 'Braintree_Exception_ServiceUnavailable');
