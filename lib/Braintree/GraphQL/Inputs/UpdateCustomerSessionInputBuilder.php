<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing UpdateCustomerSessionInput objects.
 */
class UpdateCustomerSessionInputBuilder
{
    private $merchantAccountId;
    private $sessionId;
    private $customer;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct(string $sessionId, $factory)
    {
        $this->sessionId = $sessionId;
        $this->factory = $factory;
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

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): UpdateCustomerSessionInput
    {
        $attributes = [];
        if ($this->merchantAccountId !== null) {
            $attributes['merchantAccountId'] = $this->merchantAccountId;
        }
        if ($this->sessionId !== null) {
            $attributes['sessionId'] = $this->sessionId;
        }
        if ($this->customer !== null) {
            $attributes['customer'] = $this->customer;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
