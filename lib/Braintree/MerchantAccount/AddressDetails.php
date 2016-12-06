<?php
namespace Braintree\MerchantAccount;

use Braintree\Instance;

class AddressDetails extends Instance implements \JsonSerializable
{
    protected $_attributes = [];

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
class_alias('Braintree\MerchantAccount\AddressDetails', 'Braintree_MerchantAccount_AddressDetails');
