<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Status details from a transaction
 * Creates an instance of StatusDetails, as part of a transaction response
 *
 * @package    Braintree
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $amount
 * @property-read string $status
 * @property-read string $timestamp
 * @property-read string $transactionSource
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
class_alias('Braintree\Transaction\StatusDetails', 'Braintree_Transaction_StatusDetails');
