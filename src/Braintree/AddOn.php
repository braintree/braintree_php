<?php

namespace Braintree;

class AddOn extends Modification
{
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    // static methods redirecting to gateway

    public static function all()
    {
        return Configuration::gateway()->addOn()->all();
    }
}
