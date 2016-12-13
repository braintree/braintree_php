<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * PayPal details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of PayPalDetails
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $payerEmail
 * @property-read string $paymentId
 * @property-read string $authorizationId
 * @property-read string $token
 * @property-read string $imageUrl
 * @property-read string $transactionFeeAmount
 * @property-read string $transactionFeeCurrencyIsoCode
 * @property-read string $description
 */
class PayPalDetails extends Instance implements \JsonSerializable
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
class_alias('Braintree\Transaction\PayPalDetails', 'Braintree_Transaction_PayPalDetails');
