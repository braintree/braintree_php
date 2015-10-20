<?php
namespace Braintree;

class AddOn extends Modification
{
    /**
     *
     * @param array $attributes
     * @return Braintree\AddOn
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }


    /**
     * static methods redirecting to gateway
     *
     * @return Braintree\AddOn[]
     */
    public static function all()
    {
        return Configuration::gateway()->addOn()->all();
    }
}
class_alias('Braintree\AddOn', 'Braintree_AddOn');
