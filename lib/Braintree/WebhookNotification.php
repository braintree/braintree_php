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
    const SUB_MERCHANT_ACCOUNT_APPROVED = 'sub_merchant_account_approved';
    const SUB_MERCHANT_ACCOUNT_DECLINED = 'sub_merchant_account_declined';
    const TRANSACTION_DISBURSED = 'transaction_disbursed';
    const PARTNER_MERCHANT_CONNECTED = 'partner_merchant_connected';
    const PARTNER_MERCHANT_DISCONNECTED = 'partner_merchant_disconnected';
    const PARTNER_MERCHANT_DECLINED = 'partner_merchant_declined';

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
        $digest = Braintree_Digest::hexDigest(Braintree_Configuration::privateKey(), $challenge);
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

        $payloadSignature = Braintree_Digest::hexDigest(Braintree_Configuration::privateKey(), $payload);
        if (!Braintree_Digest::secureCompare($matchingSignature, $payloadSignature)) {
            throw new Braintree_Exception_InvalidSignature("webhook notification signature invalid");
        }
    }

    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        if (isset($attributes['subject']['apiErrorResponse'])) {
            $wrapperNode = $attributes['subject']['apiErrorResponse'];
        } else {
            $wrapperNode = $attributes['subject'];
        }

        if (isset($wrapperNode['subscription'])) {
            $this->_set('subscription', Braintree_Subscription::factory($attributes['subject']['subscription']));
        }

        if (isset($wrapperNode['merchantAccount'])) {
            $this->_set('merchantAccount', Braintree_MerchantAccount::factory($wrapperNode['merchantAccount']));
        }

        if (isset($wrapperNode['transaction'])) {
            $this->_set('transaction', Braintree_Transaction::factory($wrapperNode['transaction']));
        }

        if (isset($wrapperNode['partnerMerchant'])) {
            $this->_set('partnerMerchant', Braintree_PartnerMerchant::factory($wrapperNode['partnerMerchant']));
        }

        if (isset($wrapperNode['errors'])) {
            $this->_set('errors', new Braintree_Error_ValidationErrorCollection($wrapperNode['errors']));
            $this->_set('message', $wrapperNode['message']);
        }
    }
}
