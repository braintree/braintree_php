<?php

namespace Braintree\GraphQL\Types;

use Braintree\Base;

/**
 * Represents the payment method and priority associated with a PayPal customer session.
 */
class PaymentOptions extends Base
{
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        if (isset($attributes['paymentOption'])) {
            $this->_set('paymentOption', $attributes['paymentOption']);
        }
        if (isset($attributes['recommendedPriority'])) {
            $this->_set('recommendedPriority', $attributes['recommendedPriority']);
        }
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
