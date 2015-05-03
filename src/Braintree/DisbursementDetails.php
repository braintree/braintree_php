<?php
namespace Braintree;

/**
 * Disbursement details from a transaction
 * Creates an instance of DisbursementDetails as returned from a transaction.
 *
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $settlementAmount
 * @property-read string $settlementCurrencyIsoCode
 * @property-read string $settlementCurrencyExchangeRate
 * @property-read string $fundsHeld
 * @property-read string $success
 * @property-read string $disbursementDate
 *
 * @uses Instance inherits methods
 */
class DisbursementDetails extends Instance
{
    protected $_attributes = array();

    public function isValid()
    {
        return !is_null($this->disbursementDate);
    }
}
