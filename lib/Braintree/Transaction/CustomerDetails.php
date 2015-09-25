<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Customer details from a transaction
 * Creates an instance of customer details as returned from a transaction
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $company
 * @property-read string $email
 * @property-read string $fax
 * @property-read string $firstName
 * @property-read string $id
 * @property-read string $lastName
 * @property-read string $phone
 * @property-read string $website
 *
 * @uses Instance inherits methods
 */
class CustomerDetails extends Instance
{
}
