<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing UpdateCustomerSessionInput objects.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class UpdateCustomerSessionInputBuilder
{
    private $sessionId;
    private $customer;
    private $purchaseUnits;
    private $merchantAccountId;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct(string $sessionId, $factory)
    {
        $this->sessionId = $sessionId;
        $this->factory = $factory;
    }

    /**
     * Sets the input object representing customer information relevant to the customer session.
     *
     * @param CustomerSessionInput $customer The input object representing the customer information relevant to the customer session.
     *
     * @return self
     */
    public function customer(CustomerSessionInput $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     *  Sets the Purchase Units for the items purchased.
     *
     * @param array $purchaseUnits An array of purchase units.
     *
     * @return self
     */
    public function purchaseUnits(array $purchaseUnits): self
    {
        $this->purchaseUnits = $purchaseUnits;
        return $this;
    }

    /**
     * Sets the merchant account ID.
     *
     * @param string $merchantAccountId The merchant account ID.
     *
     * @return self
     */
    public function merchantAccountId(string $merchantAccountId): self
    {
        $this->merchantAccountId = $merchantAccountId;
        return $this;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): UpdateCustomerSessionInput
    {
        $attributes = [];
        if ($this->sessionId !== null) {
            $attributes['sessionId'] = $this->sessionId;
        }
        if ($this->customer !== null) {
            $attributes['customer'] = $this->customer;
        }
        if ($this->purchaseUnits !== null) {
            $attributes['purchaseUnits'] = $this->purchaseUnits;
        }
        if ($this->merchantAccountId !== null) {
            $attributes['merchantAccountId'] = $this->merchantAccountId;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
