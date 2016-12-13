<?php
namespace Braintree;

/**
 * Partner Merchant information that is generated when a partner is connected
 * to or disconnected from a user.
 *
 * Creates an instance of PartnerMerchants
 *
 * @package    Braintree
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $merchantPublicId
 * @property-read string $publicKey
 * @property-read string $privateKey
 * @property-read string $clientSideEncryptionKey
 * @property-read string $partnerMerchantId
 */
class PartnerMerchant extends Base implements \JsonSerializable
{
    protected $_attributes = [];

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

    /**
     * create a json serializable representation of the object
     * to be passed into json_encode().
     * @ignore
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
    
}
class_alias('Braintree\PartnerMerchant', 'Braintree_PartnerMerchant');
