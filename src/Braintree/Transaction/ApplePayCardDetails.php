<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Apple Pay card details from a transaction.
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of ApplePayCardDetails.
 *
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $cardType
 * @property-read string $paymentInstrumentName
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $cardholderName
 *
 * @uses Instance inherits methods
 */
class ApplePayCardDetails extends Instance
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
