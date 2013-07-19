<?php

namespace Braintree\Transaction;

/**
 * CreditCard details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 */
use Braintree\Instance;

/**
 * creates an instance of CreditCardDetails
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 * 
 * @property-read string $bin
 * @property-read string $cardType
 * @property-read string $expirationDate
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $issuerLocation
 * @property-read string $last4
 * @property-read string $maskedNumber
 * @property-read string $token
 * @uses Instance inherits methods
 */
class CreditCardDetails extends Instance
{
    protected $_attributes = array();

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
