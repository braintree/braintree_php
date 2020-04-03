<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * CreditCard details from a transaction
 * creates an instance of CreditCardDetails
 *
 * @package    Braintree
 * @subpackage Transaction
 *
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read string $cardholderName
 * @property-read string $commercial
 * @property-read string $countryOfIssuance
 * @property-read string $customerLocation
 * @property-read string $debit
 * @property-read string $durbinRegulated
 * @property-read string $expirationDate
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $healthcare
 * @property-read string $imageUrl
 * @property-read string $issuingBank
 * @property-read string $issuerLocation
 * @property-read string $last4
 * @property-read string $maskedNumber
 * @property-read string $payroll
 * @property-read string $prepaid
 * @property-read string $productId
 * @property-read string $token
 * @property-read string $uniqueNumberIdentifier
 */
class CreditCardDetails extends Instance
{
    protected $_attributes = [];

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
        $this->_attributes['expirationDate'] = $this->expirationMonth . '/' . $this->expirationYear;
        $this->_attributes['maskedNumber'] = $this->bin . '******' . $this->last4;

    }
}
