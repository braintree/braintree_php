<?php
class Braintree_WebhookTesting
{
    public static function sampleNotification($kind, $id)
    {
        $payload = base64_encode(self::_sampleXml($kind, $id));
        $signature = Braintree_Configuration::publicKey() . "|" . Braintree_Digest::hexDigest($payload);

        return array(
            'signature' => $signature,
            'payload' => $payload
        );
    }

    private static function _sampleXml($kind, $id)
    {
        $subjectXml = self::_subscriptionSampleXml($id);
        $timestamp = self::_timestamp();
        return "
        <notification>
            <timestamp type=\"datetime\">{$timestamp}</timestamp>
            <kind>{$kind}</kind>
            <subject>{$subjectXml}</subject>
        </notification>
        ";
    }

    private static function _subscriptionSampleXml($id)
    {
        return "
        <subscription>
            <id>{$id}</id>
            <transactions type=\"array\">
            </transactions>
            <add_ons type=\"array\">
            </add_ons>
            <discounts type=\"array\">
            </discounts>
        </subscription>
        ";
    }

    private static function _timestamp()
    {
        $originalZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $timestamp = strftime('%Y-%m-%dT%TZ');
        date_default_timezone_set($originalZone);

        return $timestamp;
    }
}
