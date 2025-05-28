<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Input fields representing an amount with currency.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class MonetaryAmountInput extends Base
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
        if (isset($attributes['value'])) {
            $this->_set('value', $attributes['value']);
        }
        if (isset($attributes['currencyCode'])) {
            $this->_set('currencyCode', $attributes['currencyCode']);
        }
    }

    /**
     * Creates an instance of an MonetaryAmountInput from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return MonetaryAmountInput
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
            Util::attributesToString($this->_attributes) . ']';
    }
}
