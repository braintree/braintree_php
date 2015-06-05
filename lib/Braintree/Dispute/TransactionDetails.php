<?php
namespace Braintree\Dispute;

    /**
     * Transaction details for a dispute
     *
     * @package    Braintree
     * @copyright  2010 Braintree Payment Solutions
     */
use Braintree\Instance;

/**
 * Creates an instance of DisbursementDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read string $amount
 * @property-read string $id
 * @uses Instance inherits methods
 */
class TransactionDetails extends Instance
{
}
