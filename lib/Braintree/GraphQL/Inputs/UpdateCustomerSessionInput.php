<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Represents the input to request an update to a PayPal customer session.
 */
class UpdateCustomerSessionInput extends Base
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
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * Creates a builder instance for fluent construction of UpdateCustomerSessionInput objects.
     *
     * @param string $sessionId ID of the customer session to be updated.
     *
     * @return UpdateCustomerSessionInputBuilder
     */
    public static function builder(string $sessionId)
    {
        return new UpdateCustomerSessionInputBuilder($sessionId, function ($attributes) {
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
