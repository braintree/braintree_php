<?php
namespace Braintree\Transaction;

use Braintree\Instance;
/**
 * Amex Express Checkout card details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of AmexExpressCheckoutCardDetails
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $cardType
 * @property-read string $bin
 * @property-read string $cardMemberExpiryDate
 * @property-read string $cardMemberNumber
 * @property-read string $cardType
 * @property-read string $sourceDescription
 * @property-read string $token
 * @property-read string $imageUrl
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @uses Instance inherits methods
 */
class AmexExpressCheckoutCardDetails extends Instance implements \JsonSerializable
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
class_alias('Braintree\Transaction\AmexExpressCheckoutCardDetails', 'Braintree_Transaction_AmexExpressCheckoutCardDetails');
