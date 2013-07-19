<?php

namespace Braintree;

class Discount extends Modification
{
    public static function all()
    {
        $response = Http::get('/discounts');

        $discounts = array("discount" => $response['discounts']);

        return Util::extractAttributeAsArray(
            $discounts,
            'discount'
        );
    }
}
