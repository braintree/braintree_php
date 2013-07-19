<?php

namespace Braintree\Transaction;

/**
 * Customer details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 */
use Braintree\Instance;

/**
 * Creates an instance of customer details as returned from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 * 
 * @property-read string $company
 * @property-read string $email
 * @property-read string $fax
 * @property-read string $firstName
 * @property-read string $id
 * @property-read string $lastName
 * @property-read string $phone
 * @property-read string $website
 * @uses Instance inherits methods
 */
class CustomerDetails extends Instance
{
}
