<?php
/**
 * Address details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates an instance of AddressDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2010 Braintree Payment Solutions
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
 * @uses Braintree_Instance inherits methods
 */
class Braintree_Transaction_AddressDetails extends Braintree_Instance
{
    protected $_attributes = array();
}
