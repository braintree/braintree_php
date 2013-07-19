<?php

namespace Braintree;

class AddOn extends Modification
{
    public static function all()
    {
        $response = Http::get('/add_ons');

        $addOns = array("addOn" => $response['addOns']);

        return Util::extractAttributeAsArray(
            $addOns,
            'addOn'
        );
    }
}
