<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Status details from a transaction
 * Creates an instance of StatusDetails, as part of a transaction response
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $amount
 * @property-read string $status
 * @property-read string $timestamp
 * @property-read string $transactionSource
 * @property-read string $user
 *
 * @uses Instance inherits methods
 */
class StatusDetails extends Instance
{
}
