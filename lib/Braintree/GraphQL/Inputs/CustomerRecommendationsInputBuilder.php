<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing CustomerRecommendationsInput objects.
 */
class CustomerRecommendationsInputBuilder
{
    private $merchantAccountId;
    private $sessionId;
    private $recommendations;
    private $customer;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct(string $sessionId, array $recommendations, $factory)
    {
        $this->sessionId = $sessionId;
        $this->recommendations = $recommendations;
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
    public function build(): CustomerRecommendationsInput
    {
        $attributes = [];
        if ($this->merchantAccountId !== null) {
            $attributes['merchantAccountId'] = $this->merchantAccountId;
        }
        if ($this->sessionId !== null) {
            $attributes['sessionId'] = $this->sessionId;
        }
        if ($this->recommendations !== null) {
            $attributes['recommendations'] = $this->recommendations;
        }
        if ($this->customer !== null) {
            $attributes['customer'] = $this->customer;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
