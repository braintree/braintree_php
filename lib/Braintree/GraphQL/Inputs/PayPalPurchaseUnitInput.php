<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Payee and Amount of the item purchased.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class PayPalPurchaseUnitInput extends Base
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * Creates a builder instance for fluent construction of PayPalPurchaseUnitInput objects.
     *
     * @param MonetaryAmountInput $amount The amount with currency.
     *
     * @return PayPalPurchaseUnitInputBuilder
     */
    public static function builder(MonetaryAmountInput $amount)
    {
        return new PayPalPurchaseUnitInputBuilder($amount, function ($attributes) {
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
