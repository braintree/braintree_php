<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * iDEAL payment details from a transaction
 * creates an instance of IdealPaymentDetails
 *
 * @package    Braintree
 * @subpackage Transaction
 * @deprecated If you're looking to accept iDEAL as a payment method contact accounts@braintreepayments.com for a solution.
 *
 * @property-read string $idealPaymentId
 * @property-read string $idealTransactionId
 * @property-read string $imageUrl
 * @property-read string $maskedIban
 * @property-read string $bic
 */
// NEXT_MAJOR_VERSION Remove this class as legacy Ideal has been removed/disabled in the Braintree Gateway
class IdealPaymentDetails extends Instance
{
    protected $_attributes = [];
}
class_alias('Braintree\Transaction\IdealPaymentDetails', 'Braintree_Transaction_IdealPaymentDetails');
