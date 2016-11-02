<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * CreditCard details from a transaction
 * creates an instance of UsbankAccountDetails
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $routingNumber
 * @property-read string $last4
 * @property-read string $accountType
 * @property-read string $accountDescription
 * @property-read string $accountHolderName
 * @property-read string $token
 * @property-read string $imageUrl
 * @property-read string $bankName
 */
class UsBankAccountDetails extends Instance
{
    protected $_attributes = [];

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);

    }
}
class_alias('Braintree\Transaction\UsBankAccountDetails', 'Braintree_Transaction_UsBankAccountDetails');
