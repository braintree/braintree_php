<?php namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * PayPal details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of PayPalDetails
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $payerEmail
 * @property-read string $paymentId
 * @property-read string $authorizationId
 * @property-read string $token
 * @property-read string $imageUrl
 * @uses Instance inherits methods
 */
class PayPalDetails extends Instance
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