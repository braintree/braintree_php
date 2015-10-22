<?php
namespace Braintree\Exception;

use Braintree\Exception;

/**
 * Raised when a client library must be upgraded.
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class UpgradeRequired extends Exception
{

}
class_alias('Braintree\Exception\UpgradeRequired', 'Braintree_Exception_UpgradeRequired');
