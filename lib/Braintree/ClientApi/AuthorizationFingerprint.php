<?php

class Braintree_AuthorizationFingerprint
{

    public static function generate($params=array())
    {

        $datetime = new DateTime();
        $defaults = array(
            "merchant_id" => Braintree_Configuration::MerchantId(),
            "public_key" => Braintree_Configuration::PublicKey(),
            "created_at" => $datetime->format('c')
        );
        $data = http_build_query(array_merge($params, $defaults), null, '&');
        $hash = Braintree_Digest::hexDigestSha256($data);
        return "$hash|$data";
    }
}
