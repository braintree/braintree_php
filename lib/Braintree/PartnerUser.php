<?php
/**
 * Partner User information that is generated when a partner is connected
 * to or disconnected from a user.
 *
 * @package    Braintree
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Creates an instance of PartnerUsers
 *
 *
 * @package    Braintree
 * @copyright  2010 Braintree Payment Solutions
 *
 * @property-read string $merchantPublicId
 * @property-read string $publicKey
 * @property-read string $privateKey
 * @property-read string $partnerUserId
 * @uses Braintree_Instance inherits methods
 */
class Braintree_PartnerUser extends Braintree
{
    protected $_attributes = array();

    /**
     * @ignore
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    /**
     * @ignore
     */
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }
}
