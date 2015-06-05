<?php

namespace Braintree\MerchantAccount;

use Braintree\Base;

/**
 * Class IndividualDetails
 *
 * @package Braintree\MerchantAccount
 */
final class IndividualDetails extends Base
{
    /**
     * @param $individualAttribs
     */
    protected function _initialize($individualAttribs)
    {
        $this->_attributes = $individualAttribs;
        if (isset($individualAttribs['address'])) {
            $this->_set('addressDetails', new AddressDetails($individualAttribs['address']));
        }
    }

    /**
     * @param $attributes
     * @return IndividualDetails
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
