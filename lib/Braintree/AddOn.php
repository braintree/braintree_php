<?php
class Braintree_AddOn extends Braintree_Modification
{
    public static function all()
    {
        $response = Braintree_Http::get('/add_ons');

        $modifications = array("modification" => $response['modifications']);

        return Braintree_Util::extractAttributeAsArray(
            $modifications,
            'modification'
        );
    }
}
