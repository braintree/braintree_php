<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing PayPalPayeeInput objects.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class PayPalPayeeInputBuilder
{
    private $emailAddress;
    private $clientId;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Sets the email address of this merchant.
     *
     * @param string $emailAddress The email address.
     *
     * @return self
     */
    public function emailAddress($emailAddress): self
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * Sets the public ID for the payee- or merchant-created app.
     * Introduced to support use cases, such as Braintree integration with PayPal,
     * where payee 'emailAddress' or 'merchantId' is not available.
     *
     * @param string $clientId The public ID for the payee.
     *
     * @return self
     */
    public function clientId($clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): PayPalPayeeInput
    {
        $attributes = [];
        if ($this->emailAddress !== null) {
            $attributes['emailAddress'] = $this->emailAddress;
        }
        if ($this->clientId !== null) {
            $attributes['clientId'] = $this->clientId;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
