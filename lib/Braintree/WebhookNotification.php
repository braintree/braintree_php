<?php
class Braintree_WebhookNotification extends Braintree
{
    const SUBSCRIPTION_CANCELED = 'subscription_canceled';
    const SUBSCRIPTION_CHARGED_SUCCESSFULLY = 'subscription_charged_successfully';
    const SUBSCRIPTION_CHARGED_UNSUCCESSFULLY = 'subscription_charged_unsuccessfully';
    const SUBSCRIPTION_EXPIRED = 'subscription_expired';
    const SUBSCRIPTION_TRIAL_ENDED = 'subscription_trial_ended';
    const SUBSCRIPTION_WENT_ACTIVE = 'subscription_went_active';
    const SUBSCRIPTION_WENT_PAST_DUE = 'subscription_went_past_due';

    public static function parse($signature, $payload)
    {
        self::_validateSignature($signature, $payload);

        $xml = base64_decode($payload);
        $attributes = Braintree_Xml::buildArrayFromXml($xml);
        return self::factory($attributes['notification']);
    }

    public static function verify($challenge)
    {
        $publicKey = Braintree_Configuration::publicKey();
        $digest = Braintree_Digest::hexDigest($challenge);
        return "{$publicKey}|{$digest}";
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    private static function _matchingSignature($signaturePairs)
    {
        foreach ($signaturePairs as $pair)
        {
            $components = preg_split("/\|/", $pair);
            if ($components[0] == Braintree_Configuration::publicKey()) {
                return $components[1];
            }
        }

        return null;
    }

    private static function _validateSignature($signature, $payload)
    {
        $signaturePairs = preg_split("/&/", $signature);
        $matchingSignature = self::_matchingSignature($signaturePairs);

        $payloadSignature = Braintree_Digest::hexDigest($payload);
        if (!Braintree_Digest::secureCompare($matchingSignature, $payloadSignature)) {
            throw new Braintree_Exception_InvalidSignature("webhook notification signature invalid");
        }
    }

    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
        if (isset($attributes['subject']) && isset($attributes['subject']['subscription'])) {
            $this->_set('subscription', Braintree_Subscription::factory($attributes['subject']['subscription']));
        }
    }
}
