<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Creates an instance of AddressDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 *
 * @property-read string $firstName
 * @property-read string $lastName
 * @property-read string $company
 * @property-read string $streetAddress
 * @property-read string $extendedAddress
 * @property-read string $locality
 * @property-read string $region
 * @property-read string $postalCode
 * @property-read string $countryName
 * @property-read string $countryCodeNumeric
 * @property-read string $countryCodeAlpha2
 * @property-read string $countryCodeAlpha3
 */
class AddressDetails extends Instance
{
    protected $_attributes = [];
}
class_alias('Braintree\Transaction\AddressDetails', 'Braintree_Transaction_AddressDetails');
