<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Phone number input for PayPal customer session.
 */
class PhoneInput extends Base
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
        if (isset($attributes['countryPhoneCode'])) {
            $this->_set('countryPhoneCode', $attributes['countryPhoneCode']);
        }
        if (isset($attributes['phoneNumber'])) {
            $this->_set('phoneNumber', $attributes['phoneNumber']);
        }
        if (isset($attributes['extensionNumber'])) {
            $this->_set('extensionNumber', $attributes['extensionNumber']);
        }
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * Creates a builder instance for fluent construction of PhoneInput objects.
     *
     * @return PhoneInputBuilder
     */
    public static function builder()
    {
        return new PhoneInputBuilder(function ($attributes) {
            return self::factory($attributes);
        });
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
            Util::attributesToString($this->_attributes, true) . ']';
    }
}
