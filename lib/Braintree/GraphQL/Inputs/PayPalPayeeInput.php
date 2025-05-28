<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * The details for the merchant who receives the funds and fulfills the order. The merchant is also known as the payee.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class PayPalPayeeInput extends Base
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
        if (isset($attributes['emailAddress'])) {
            $this->_set('emailAddress', $attributes['emailAddress']);
        }
        if (isset($attributes['clientId'])) {
            $this->_set('clientId', $attributes['clientId']);
        }
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * Creates a builder instance for fluent construction of PayPalPayeeInput objects.
     *
     * @return PayPalPayeeInputBuilder
     */
    public static function builder()
    {
        return new PayPalPayeeInputBuilder(function ($attributes) {
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
