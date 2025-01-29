<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Customer identifying information for a PayPal customer session.
 */
class CustomerSessionInput extends Base
{
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        if (isset($attributes['email'])) {
            $this->_set('email', $attributes['email']);
        }
        if (isset($attributes['phone'])) {
            $this->_set('phone', $attributes['phone']);
        }
        if (isset($attributes['deviceFingerprintId'])) {
            $this->_set('deviceFingerprintId', $attributes['deviceFingerprintId']);
        }
        if (isset($attributes['paypalAppInstalled'])) {
            $this->_set('paypalAppInstalled', $attributes['paypalAppInstalled']);
        }
        if (isset($attributes['venmoAppInstalled'])) {
            $this->_set('venmoAppInstalled', $attributes['venmoAppInstalled']);
        }
        if (isset($attributes['userAgent'])) {
            $this->_set('userAgent', $attributes['userAgent']);
        }
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

     /**
     * Creates a builder instance for fluent construction of CustomerSessionInput objects.
     *
     * @return CustomerSessionInputBuilder
     */
    public static function builder()
    {
        return new CustomerSessionInputBuilder(function ($attributes) {
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
