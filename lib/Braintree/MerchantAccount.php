<?php //phpcs:disable

namespace Braintree;

/**
 * See our {@link https://developer.paypal.com/braintree/docs/reference/response/merchant-account developer docs} for information on attributes
 */
class MerchantAccount extends Base
{
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';

    const FUNDING_DESTINATION_BANK = 'bank';
    const FUNDING_DESTINATION_EMAIL = 'email';
    const FUNDING_DESTINATION_MOBILE_PHONE = 'mobile_phone';

    /**
     * Creates an instance from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return MerchantAccount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    protected function _initialize($merchantAccountAttribs)
    {
        $this->_attributes = $merchantAccountAttribs;

    }


    // static methods redirecting to gateway


    public static function find($merchant_account_id)
    {
        return Configuration::gateway()->merchantAccount()->find($merchant_account_id);
    }

}
