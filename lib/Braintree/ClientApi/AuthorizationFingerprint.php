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
        $data = array_merge($params, $defaults);
        $signatureService = new Braintree_SignatureService(
            Braintree_Configuration::privateKey(),
            "Braintree_Digest::hexDigestSha256"
        );
        return $signatureService->sign($data);
    }
}
