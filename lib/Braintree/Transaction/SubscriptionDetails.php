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
 * @property-read string $billing_period_start_date
 * @property-read string $billing_period_end_date
 */
class SubscriptionDetails extends Instance
{
}
