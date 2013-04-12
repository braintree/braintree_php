<?php
/**
 * Deposit details from a transaction
 *
 * @package    Braintree
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates an instance of DepositDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read string $settlementAmount
 * @property-read string $settlementCurrencyIsoCode
 * @property-read string $settlementCurrencyExchangeRate
 * @property-read string $settlementFundsHeld
 * @property-read string $depositDate
 * @property-read string $disbursedAt
 * @uses Braintree_Instance inherits methods
 */
class Braintree_DepositDetails extends Braintree_Instance
{
    protected $_attributes = array();

    function isValid() {
        return !is_null($this->depositDate);
    }
}
