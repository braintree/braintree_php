<?php
namespace Braintree\MerchantAccount;

use Braintree\Base;

class BusinessDetails extends Base implements \JsonSerializable
{
    protected function _initialize($businessAttribs)
    {
        $this->_attributes = $businessAttribs;
        if (isset($businessAttribs['address'])) {
            $this->_set('addressDetails', new AddressDetails($businessAttribs['address']));
        }
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
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
class_alias('Braintree\MerchantAccount\BusinessDetails', 'Braintree_MerchantAccount_BusinessDetails');
