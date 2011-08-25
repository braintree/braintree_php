<?php
class Braintree_Discount extends Braintree_Modification
{
    public static function all()
    {
        $response = Braintree_Http::get('/discounts');

        $modifications = array("modification" => $response['modifications']);

        return Braintree_Util::extractAttributeAsArray(
            $modifications,
            'modification'
        );
    }
}
