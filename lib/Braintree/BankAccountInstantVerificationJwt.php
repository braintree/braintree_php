<?php

namespace Braintree;

/**
 * Represents a Bank Account Instant Verification JWT containing a JWT.
 *
 * @property-read string $jwt
 */
class BankAccountInstantVerificationJwt extends Base
{
    protected $_attributes = [
        'jwt'
    ];

    /**
     * factory method: returns an instance of BankAccountInstantVerificationJwt
     * from given attributes
     *
     * @param array $attributes attributes for the verification JWT
     *
     * @return BankAccountInstantVerificationJwt
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    /**
     * Initializes the instance with given attributes
     *
     * @param array $attributes
     */
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * Returns the JWT for Bank Account Instant Verification.
     *
     * @return string the JWT
     */
    public function getJwt()
    {
        return $this->jwt;
    }
}
