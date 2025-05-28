<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing CustomerRecommendationsInput objects.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class CustomerRecommendationsInputBuilder
{
    private $sessionId;
    private $customer;
    private $purchaseUnits;
    private $domain;
    private $merchantAccountId;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($factory)
    {
        $this->factory = $factory;
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
    public function build(): CustomerRecommendationsInput
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
        if ($this->domain !== null) {
            $attributes['domain'] = $this->domain;
        }
        if ($this->merchantAccountId !== null) {
            $attributes['merchantAccountId'] = $this->merchantAccountId;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
