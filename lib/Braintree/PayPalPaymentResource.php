<?php // phpcs:disable PEAR.Commenting

namespace Braintree;

class PayPalPaymentResource extends Base
{
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    public static function update($attribs)
    {
        return Configuration::gateway()->payPalPaymentResource()->update($attribs);
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }
}
