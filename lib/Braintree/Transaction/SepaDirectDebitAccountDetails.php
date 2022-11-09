<?php

namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * SEPA Direct Debit Account details from a transaction
 */

/**
 * Creates an instance of SepaDirectDebitAccountDetails
 *
 * See our {@link https://developer.paypal.com/braintree/docs/reference/response/transaction#sepa_direct_debit_account_details developer docs} for information on attributes
 */
class SepaDirectDebitAccountDetails extends Instance
{
    protected $_attributes = [];

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }
}
