<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Coinbase details from a transaction.
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of Coinbase.
 *
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $userId
 * @property-read string $userName
 * @property-read string $userEmail
 * @property-read string $imageUrl
 *
 * @uses Instance inherits methods
 */
class CoinbaseDetails extends Instance
{
    protected $_attributes = array();

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }
}
