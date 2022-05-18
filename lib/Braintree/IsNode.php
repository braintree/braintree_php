<?php

namespace Braintree;

/**
 * Braintree IsNode
 * IsNode is an object for search elements sent to the Braintree API
 */
class IsNode
{
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($name)
    {
        $this->name = $name;
        $this->searchTerms = [];
    }

    /**
     * Sets the value of the object's "is" key to a string of $value
     */
    public function is(string $value): self
    {
        $this->searchTerms['is'] = $value;

        return $this;
    }

    /**
     * The searchTerms
     */
    public function toParam(): array
    {
        return $this->searchTerms;
    }
}
