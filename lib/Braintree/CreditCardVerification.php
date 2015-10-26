<?php
namespace Braintree;

class CreditCardVerification extends Result\CreditCardVerification
{
    public static function factory($attributes)
    {
        $instance = new self($attributes);
        return $instance;
    }


    // static methods redirecting to gateway

    public static function fetch($query, $ids)
    {
        return Configuration::gateway()->creditCardVerification()->fetch($query, $ids);
    }

    public static function search($query)
    {
        return Configuration::gateway()->creditCardVerification()->search($query);
    }
}
class_alias('Braintree\CreditCardVerification', 'Braintree_CreditCardVerification');
