<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Coinbase details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of Coinbase
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $userId
 * @property-read string $userName
 * @property-read string $userEmail
 * @property-read string $imageUrl
 */
class CoinbaseDetails extends Instance implements \JsonSerializable
{
    protected $_attributes = [];

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

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
class_alias('Braintree\Transaction\CoinbaseDetails', 'Braintree_Transaction_CoinbaseDetails');
