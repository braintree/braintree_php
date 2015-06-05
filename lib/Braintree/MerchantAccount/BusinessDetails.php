<?php
namespace Braintree\MerchantAccount;

use Braintree\Base;


/**
 * Class BusinessDetails
 *
 * @package Braintree\MerchantAccount
 */
final class BusinessDetails extends Base
{
    /**
     * @param $businessAttribs
     */
    protected function _initialize($businessAttribs)
    {
        $this->_attributes = $businessAttribs;
        if (isset($businessAttribs['address'])) {
            $this->_set('addressDetails', new AddressDetails($businessAttribs['address']));
        }
    }

    /**
     * @param $attributes
     * @return BusinessDetails
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
