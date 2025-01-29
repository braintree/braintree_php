<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Represents the input to request the creation of a PayPal customer session.
 */
class CreateCustomerSessionInput extends Base
{
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        if (isset($attributes['merchantAccountId'])) {
            $this->_set('merchantAccountId', $attributes['merchantAccountId']);
        }
        if (isset($attributes['sessionId'])) {
            $this->_set('sessionId', $attributes['sessionId']);
        }
        if (isset($attributes['customer'])) {
            $this->_set('customer', $attributes['customer']);
        }
        if (isset($attributes['domain'])) {
            $this->_set('domain', $attributes['domain']);
        }
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * Creates a builder instance for fluent construction of CreateCustomerSessionInput objects.
     *
     * @return CreateCustomerSessionInputBuilder
     */
    public static function builder()
    {
        return new CreateCustomerSessionInputBuilder(function ($attributes) {
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
