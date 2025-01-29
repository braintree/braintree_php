<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing CreateCustomerSessionInput objects.
 */
class CreateCustomerSessionInputBuilder
{
    private $merchantAccountId;
    private $sessionId;
    private $customer;
    private $domain;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($factory)
    {
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
     * Sets the customer session ID.
     *
     * @param string $sessionId The customer session ID.
     *
     * @return self
     */
    public function sessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;
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

    /**
     * Sets the customer domain.
     *
     * @param string $domain The customer domain.
     *
     * @return self
     */
    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): CreateCustomerSessionInput
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
        if ($this->domain !== null) {
            $attributes['domain'] = $this->domain;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
