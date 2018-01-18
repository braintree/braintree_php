<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Line item associated with a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 */

/**
 * creates an instance of LineItem
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 *
 * @property-read string $quantity
 * @property-read string $name
 * @property-read string $description
 * @property-read string $kind
 * @property-read string $unitAmount
 * @property-read string $unitTaxAmount
 * @property-read string $totalAmount
 * @property-read string $discountAmount
 * @property-read string $unitOfMeasure
 * @property-read string $productCode
 * @property-read string $commodityCode
 * @property-read string $url
 */
class LineItem extends Instance
{
    // LineItem Kinds
    const CREDIT = 'credit';
    const DEBIT = 'debit';

    protected $_attributes = [];

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }
}
class_alias('Braintree\Transaction\LineItem', 'Braintree_Transaction_LineItem');
