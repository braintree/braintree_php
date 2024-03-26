<?php

namespace Braintree;

/**
 * Braintre ClientToken create and manage client tokens for authorization
 */
class ClientToken
{
    const DEFAULT_VERSION = 2;

    /**
     * static method redirecting to gateway class
     *
     * @param array $params to be supplied in api request
     *
     * @see ClientTokenGateway::generate()
     *
     * @return string
     */
    public static function generate($params = [])
    {
        return Configuration::gateway()->clientToken()->generate($params);
    }

    // NEXT_MAJOR_VERSION Remove this method
    /**
     * static method redirecting to gateway class
     *
     * @param array $params to be verified
     *
     * @see ClientTokenGateway::conditionallyVerifyKeys()
     *
     * @deprecated
     *
     * @return array
     */
    public static function conditionallyVerifyKeys($params)
    {
        return Configuration::gateway()->clientToken()->conditionallyVerifyKeys($params);
    }

    /**
     * static method redirecting to gateway class
     *
     * @see ClientTokenGateway::generateSignature()
     *
     * @return array
     */
    public static function generateSignature()
    {
        return Configuration::gateway()->clientToken()->generateSignature();
    }

    // NEXT_MAJOR_VERSION Remove this method
    // Replaced with generateSignature
    /**
     * static method redirecting to gateway class
     *
     * @see ClientTokenGateway::generateWithCustomerIdSignature()
     *
     * @deprecated
     *
     * @return array
     */
    public static function generateWithCustomerIdSignature()
    {
        return Configuration::gateway()->clientToken()->generateWithCustomerIdSignature();
    }

    // NEXT_MAJOR_VERSION Remove this method
    // Replaced with generateSignature
    /**
     * static method redirecting to gateway class
     *
     * @see ClientTokenGateway::generateWithoutCustomerIdSignature()
     *
     * @deprecated
     *
     * @return array
     */
    public static function generateWithoutCustomerIdSignature()
    {
        return Configuration::gateway()->clientToken()->generateWithoutCustomerIdSignature();
    }
}
