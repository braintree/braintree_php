<?php

namespace Braintree\GraphQL\Types;

use Braintree\Base;

/**
 * Represents the customer recommendations associated with a PayPal customer session.
 */
class CustomerRecommendationsPayload extends Base
{
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        if (isset($attributes['isInPayPalNetwork'])) {
            $this->_set('isInPayPalNetwork', $attributes['isInPayPalNetwork']);
        }
        if (isset($attributes['recommendations'])) {
            $this->_set('recommendations', $attributes['recommendations']);
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
