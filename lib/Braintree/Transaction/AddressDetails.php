<?php
namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Creates an instance of AddressDetails as returned from a transaction
 *
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
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
 *
 * @uses Instance inherits methods
 */
class AddressDetails extends Instance
{
    protected $_attributes = array();
}
