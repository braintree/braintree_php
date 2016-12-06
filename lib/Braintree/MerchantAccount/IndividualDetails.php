<?php
namespace Braintree\MerchantAccount;

use Braintree\Base;

class IndividualDetails extends Base implements \JsonSerializable
{
    protected function _initialize($individualAttribs)
    {
        $this->_attributes = $individualAttribs;
        if (isset($individualAttribs['address'])) {
            $this->_set('addressDetails', new AddressDetails($individualAttribs['address']));
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
class_alias('Braintree\MerchantAccount\IndividualDetails', 'Braintree_MerchantAccount_IndividualDetails');
