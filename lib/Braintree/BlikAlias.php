<?php

namespace Braintree;

/**
 * BlikAlias class
 * For more information about blik aliases
 * For more information on BlikAliases, see https://developer.paypal.com/braintree/docs/guides/local-payment-methods/blik-one-click
 *
 * @see AddOn
 */
class BlikAlias extends Base
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * Creates an instance from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return BlikAlias
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
        return get_called_class() . '[' . Util::attributesToString($this->_attributes) . ']';
    }
}
