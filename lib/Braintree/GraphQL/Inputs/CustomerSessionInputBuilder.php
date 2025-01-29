<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing CustomerSessionInput objects.
 */
class CustomerSessionInputBuilder
{
    private $email;

    private $phone;
    private $deviceFingerprintId;
    private $paypalAppInstalled;
    private $venmoAppInstalled;
    private $userAgent;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Sets the customer email address.
     *
     * @param string $email The customer email address.
     *
     * @return self
     */
    public function email(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Sets the customer phone number input object.
     *
     * @param PhoneInput $phone The input object representing the customer phone number.
     *
     * @return self
     */
    public function phone(PhoneInput $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Sets the customer device fingerprint ID.
     *
     * @param string $deviceFingerprintId The customer device fingerprint ID.
     *
     * @return self
     */
    public function deviceFingerprintId(string $deviceFingerprintId): self
    {
        $this->deviceFingerprintId = $deviceFingerprintId;
        return $this;
    }

    /**
     * Sets whether the PayPal app is installed on the customer's device.
     *
     * @param bool $paypalAppInstalled True if the PayPal app is installed, false otherwise.
     *
     * @return self
     */
    public function paypalAppInstalled(bool $paypalAppInstalled): self
    {
        $this->paypalAppInstalled = $paypalAppInstalled;
        return $this;
    }

    /**
     * Sets whether the Venmo app is installed on the customer's device.
     *
     * @param bool $venmoAppInstalled True if the Venmo app is installed, false otherwise.
     *
     * @return self
     */
    public function venmoAppInstalled(bool $venmoAppInstalled): self
    {
        $this->venmoAppInstalled = $venmoAppInstalled;
        return $this;
    }

    /**
     * Sets the user agent from the request originating from the customer's device.
     * This will be used to identify the customer's operating system and browser versions.
     *
     * @param bool $userAgent The user agent.
     *
     * @return self
     */
    public function userAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): CustomerSessionInput
    {
        $attributes = [];
        if ($this->email !== null) {
            $attributes['email'] = $this->email;
        }
        if ($this->phone !== null) {
            $attributes['phone'] = $this->phone;
        }
        if ($this->deviceFingerprintId !== null) {
            $attributes['deviceFingerprintId'] = $this->deviceFingerprintId;
        }
        if ($this->paypalAppInstalled !== null) {
            $attributes['paypalAppInstalled'] = $this->paypalAppInstalled;
        }
        if ($this->venmoAppInstalled !== null) {
            $attributes['venmoAppInstalled'] = $this->venmoAppInstalled;
        }
        if ($this->userAgent !== null) {
            $attributes['userAgent'] = $this->userAgent;
        }
        $func = $this->factory;
        return $func($attributes);
    }
}
