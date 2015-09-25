<?php
namespace Braintree\Subscription;

use Braintree\Instance;

/**
 * Status details from a subscription
 * Creates an instance of StatusDetails, as part of a subscription response
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $price
 * @property-read string $balance
 * @property-read string $status
 * @property-read string $timestamp
 * @property-read string $subscriptionSource
 * @property-read string $user
 *
 * @uses Instance inherits methods
 */
class StatusDetails extends Instance
{
}
