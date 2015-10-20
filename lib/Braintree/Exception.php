<?php
namespace Braintree;

/**
 * super class for all Braintree exceptions
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Exception extends \Exception
{
}
class_alias('Braintree\Exception', 'Braintree_Exception');
