<?php
namespace Braintree;

class ClientToken
{
    const DEFAULT_VERSION = 2;


    // static methods redirecting to gateway

    /**
     *
     * @param array $params
     * @return array
     */
    public static function generate($params = array())
    {
        return Configuration::gateway()->clientToken()->generate($params);
    }

    /**
     *
     * @param type $params
     * @throws InvalidArgumentException
     */
    public static function conditionallyVerifyKeys($params)
    {
        return Configuration::gateway()->clientToken()->conditionallyVerifyKeys($params);
    }

    /**
     *
     * @param type $params
     * @throws InvalidArgumentException
     */
    public static function generateWithCustomerIdSignature()
    {
        return Configuration::gateway()->clientToken()->generateWithCustomerIdSignature();
    }

    /**
     *
     * @param type $params
     * @throws InvalidArgumentException
     */
    public static function generateWithoutCustomerIdSignature()
    {
        return Configuration::gateway()->clientToken()->generateWithoutCustomerIdSignature();
    }
}
