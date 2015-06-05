<?php namespace Braintree;

class ClientToken
{
    const DEFAULT_VERSION = 2;


    // static methods redirecting to gateway

    public static function generate($params = array())
    {
        return Configuration::gateway()->clientToken()->generate($params);
    }

    public static function conditionallyVerifyKeys($params)
    {
        return Configuration::gateway()->clientToken()->conditionallyVerifyKeys($params);
    }

    public static function generateWithCustomerIdSignature()
    {
        return Configuration::gateway()->clientToken()->generateWithCustomerIdSignature();
    }

    public static function generateWithoutCustomerIdSignature()
    {
        return Configuration::gateway()->clientToken()->generateWithoutCustomerIdSignature();
    }
}
