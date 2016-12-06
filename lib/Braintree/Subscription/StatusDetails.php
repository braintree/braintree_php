<?php
namespace Braintree\Subscription;

use Braintree\Instance;

/**
 * Status details from a subscription
 * Creates an instance of StatusDetails, as part of a subscription response
 *
 * @package    Braintree
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $price
 * @property-read string $currencyIsoCode
 * @property-read string $planId
 * @property-read string $balance
 * @property-read string $status
 * @property-read string $timestamp
 * @property-read string $subscriptionSource
 * @property-read string $user
 */
class StatusDetails extends Instance implements \JsonSerializable
{

    /**
     * create a json serializable representation of the object
     * to be passed into json_encode().
     * @ignore
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }

}
class_alias('Braintree\Subscription\StatusDetails', 'Braintree_Subscription_StatusDetails');
