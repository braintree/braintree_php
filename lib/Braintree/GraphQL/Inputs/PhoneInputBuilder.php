<?php

namespace Braintree\GraphQL\Inputs;

/**
 * This class provides a fluent interface for constructing PhoneInput objects.
 */

class PhoneInputBuilder
{
    private $countryPhoneCode;
    private $phoneNumber;
    private $extensionNumber;

    private $factory;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Sets the country phone code.
     *
     * @param string $countryPhoneCode The country phone code.
     *
     * @return self
     */
    public function countryPhoneCode(string $countryPhoneCode): self
    {
        $this->countryPhoneCode = $countryPhoneCode;
        return $this;
    }

    /**
     * Sets the phone number.
     *
     * @param string $phoneNumber The phone number.
     *
     * @return self
     */
    public function phoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * Sets the extension number.
     *
     * @param string $extensionNumber The extension number.
     *
     * @return self
     */
    public function extensionNumber(string $extensionNumber): self
    {
        $this->extensionNumber = $extensionNumber;
        return $this;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function build(): PhoneInput
    {
        $attributes = [];
        if ($this->countryPhoneCode !== null) {
            $attributes['countryPhoneCode'] = $this->countryPhoneCode;
        }
        if ($this->phoneNumber !== null) {
            $attributes['phoneNumber'] = $this->phoneNumber;
        }
        if ($this->extensionNumber !== null) {
            $attributes['extensionNumber'] = $this->extensionNumber;
        }
        $func = $this->factory;
        return  $func($attributes);
    }
}
