<?php

namespace Braintree;

/**
 * Braintree MetaCheckoutToken module
 * Creates and manages Braintree MetaCheckoutTokens
 */
class MetaCheckoutToken extends Base
{
    /* instance methods */
    /**
     * returns false if default is null or false
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * checks whether the card is expired based on the current date
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     * sets instance properties from an array of values
     *
     * @param array $creditCardAttribs array of creditcard data
     *
     * @return void
     */
    protected function _initialize($creditCardAttribs)
    {
        // set the attributes
        $this->_attributes = $creditCardAttribs;


        $this->_set('expirationDate', $this->expirationMonth . '/' . $this->expirationYear);
        $this->_set('maskedNumber', $this->bin . '******' . $this->last4);

        if (isset($creditCardAttribs['verifications']) && count($creditCardAttribs['verifications']) > 0) {
            $verifications = $creditCardAttribs['verifications'];
            usort($verifications, [$this, '_compareCreatedAtOnVerifications']);

            $this->_set('verification', CreditCardVerification::factory($verifications[0]));
        }
    }

    private function _compareCreatedAtOnVerifications($verificationAttrib1, $verificationAttrib2)
    {
        return ($verificationAttrib2['createdAt'] < $verificationAttrib1['createdAt']) ? -1 : 1;
    }

    /**
     * returns false if comparing object is not a MetaCheckoutToken,
     * or is a MetaCheckoutToken with a different id
     *
     * @param object $otherMetaCheckoutToken customer to compare against
     *
     * @return boolean
     */
    public function isEqual($otherMetaCheckoutToken)
    {
        return !($otherMetaCheckoutToken instanceof self) ? false : $this->token === $otherMetaCheckoutToken->token;
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }

    /**
     * Creates an instance from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return MetaCheckoutToken
     */
    public static function factory($attributes)
    {
        $defaultAttributes = [
            'bin' => '',
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => '',
        ];

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }
}
