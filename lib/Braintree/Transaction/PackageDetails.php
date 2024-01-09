<?php

namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Package details from a transaction
 * Creates an instance of PackageDetails, as part of a transaction response
 */
class PackageDetails extends Instance
{
    protected $_attributes = [];

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }
}
