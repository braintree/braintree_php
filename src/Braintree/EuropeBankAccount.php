<?php
namespace Braintree;

/**
 * Braintree EuropeBankAccount module
 * Creates and manages Braintree Europe Bank Accounts
 *
 * <b>== More information ==</b>
 *
 * See {@link https://developers.braintreepayments.com/javascript+php}<br />
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $default
 * @property-read string $masked-iban
 * @property-read string $bic
 * @property-read string $mandate-reference-number
 * @property-read string $account-holder-name
 * @property-read string $image-url
 */
class EuropeBankAccount extends Braintree
{
    /* instance methods */
    /**
     * returns false if default is null or false
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     *  factory method: returns an instance of Braintree\EuropeBankAccount
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree\EuropeBankAccount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $europeBankAccountAttribs array of EuropeBankAccount properties
     * @return none
     */
    protected function _initialize($europeBankAccountAttribs)
    {
        $this->_attributes = $europeBankAccountAttribs;
    }
}
