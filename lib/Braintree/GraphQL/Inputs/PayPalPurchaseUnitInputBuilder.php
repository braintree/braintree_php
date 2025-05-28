<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;

/**
 * This class provides a fluent interface for constructing PayPalPurchaseUnitInput objects.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class PayPalPurchaseUnitInputBuilder
{
    private $amount;
    private $payee;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct(MonetaryAmountInput $amount, $factory)
    {
        $this->amount = $amount;
        $this->factory = $factory;
    }

    /**
     * Sets the PayPal payee.
     *
     * @param PayPalPayeeInput $payee The PayPal payee.
     *
     * @return self
     */
    public function payee(PayPalPayeeInput $payee): self
    {
        $this->payee = $payee;
        return $this;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): PayPalPurchaseUnitInput
    {
        $attributes = [];
        if ($this->payee !== null) {
            $attributes['payee'] = $this->payee;
        }
        if ($this->amount !== null) {
            $attributes['amount'] = $this->amount;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
