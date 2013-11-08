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

        if (array_key_exists("makeDefault", $params)) {
            $params["credit_card[options][make_default]"] = $params["makeDefault"];
            unset($params["makeDefault"]);
        }

        if (array_key_exists("verifyCard", $params)) {
            $params["credit_card[options][verify_card]"] = $params["verifyCard"];
            unset($params["verifyCard"]);
        }

        if (array_key_exists("failOnDuplicatePaymentMethod", $params)) {
            $params["credit_card[options][fail_on_duplicate_payment_method]"] = $params["failOnDuplicatePaymentMethod"];
            unset($params["failOnDuplicatePaymentMethod"]);
        }

        $payload = array_merge($params, $defaults);
        $signatureService = new Braintree_SignatureService(
            Braintree_Configuration::privateKey(),
            "Braintree_Digest::hexDigestSha256"
        );
        return $signatureService->sign($payload);
    }
}
