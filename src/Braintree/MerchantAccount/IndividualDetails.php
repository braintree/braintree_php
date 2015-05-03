<?php
namespace Braintree\MerchantAccount;

use Braintree\Braintree;

final class IndividualDetails extends Braintree
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
}
