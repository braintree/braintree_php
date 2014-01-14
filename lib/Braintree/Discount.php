<?php
class Braintree_Discount extends Braintree_Modification
{
    public static function all()
    {
        $response = Braintree_Http::get('/discounts');

        $discounts = array("discount" => $response['discounts']);

        return Braintree_Util::extractAttributeAsArray(
            $discounts,
            'discount'
        );
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
