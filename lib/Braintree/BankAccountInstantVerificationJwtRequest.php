<?php

namespace Braintree;

/**
 * Provides a fluent interface to build requests for creating Bank Account Instant Verification JWTs.
 */
class BankAccountInstantVerificationJwtRequest
{
    private $_businessName;
    private $_returnUrl;
    private $_cancelUrl;

    /**
     * Sets the officially registered business name for the merchant.
     *
     * @param string $businessName the business name
     *
     * @return BankAccountInstantVerificationJwtRequest
     */
    public function businessName($businessName)
    {
        $this->_businessName = $businessName;
        return $this;
    }

    /**
     * Sets the URL to redirect the consumer after successful account selection.
     *
     * @param string $returnUrl the return URL
     *
     * @return BankAccountInstantVerificationJwtRequest
     */
    public function returnUrl($returnUrl)
    {
        $this->_returnUrl = $returnUrl;
        return $this;
    }

    /**
     * Sets the URL to redirect the consumer upon cancellation of the Open Banking flow.
     *
     * @param string $cancelUrl the cancel URL
     *
     * @return BankAccountInstantVerificationJwtRequest
     */
    public function cancelUrl($cancelUrl)
    {
        $this->_cancelUrl = $cancelUrl;
        return $this;
    }


    /**
     * Gets the business name.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->_businessName;
    }

    /**
     * Gets the return URL.
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_returnUrl;
    }

    /**
     * Gets the cancel URL.
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_cancelUrl;
    }


    /**
     * Converts the request to GraphQL variables format
     *
     * @return array
     */
    public function toGraphQLVariables()
    {
        $variables = [];
        $input = [];

        if ($this->_businessName !== null) {
            $input['businessName'] = $this->_businessName;
        }
        if ($this->_returnUrl !== null) {
            $input['returnUrl'] = $this->_returnUrl;
        }
        if ($this->_cancelUrl !== null) {
            $input['cancelUrl'] = $this->_cancelUrl;
        }

        $variables['input'] = $input;
        return $variables;
    }
}
