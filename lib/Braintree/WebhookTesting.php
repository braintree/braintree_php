<?php
class Braintree_WebhookTesting
{
    public static function sampleNotification($kind, $data)
    {
        if (is_string($data)) {
            $data = array("id" => $data);
        }

        $payload = base64_encode(self::_sampleXml($kind, $data));
        $signature = Braintree_Configuration::publicKey() . "|" . Braintree_Digest::hexDigest($payload);

        return array(
            'signature' => $signature,
            'payload' => $payload
        );
    }

    private static function _sampleXml($kind, $data)
    {
        switch ($kind) {
            case Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED:
                $subjectXml = self::_merchantAccountSampleXml($data);
                break;
            case Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED:
                $subjectXml = self::_merchantAccountDeclinedSampleXml($data);
                break;
            default:
                $subjectXml = self::_subscriptionSampleXml($data);
                break;
        }
        $timestamp = self::_timestamp();
        return "
        <notification>
            <timestamp type=\"datetime\">{$timestamp}</timestamp>
            <kind>{$kind}</kind>
            <subject>{$subjectXml}</subject>
        </notification>
        ";
    }

    private static function _merchantAccountSampleXml($data)
    {
        return "
        <merchant_account>
            <id>{$data["id"]}</id>
            <master_merchant_account>
                <id>{$data["master_merchant_account"]["id"]}</id>
                <status>{$data["master_merchant_account"]["status"]}</status>
            </master_merchant_account>
            <status>{$data["status"]}</status>
        </merchant_account>
        ";
    }

    private static function _merchantAccountDeclinedSampleXml($data)
    {
        $errorSampleXml = self::_errorsSampleXml($data['errors']);
        $merchantAccountSampleXml = self::_merchantAccountSampleXml($data['merchant_account']);
        return "
        <api-error-response>
            <message>{$data['message']}</message>
            <errors>
                <merchant-account>
                    <errors type='array'>
                    {$errorSampleXml}
                    </errors>
                </merchant-account>
            </errors>
            {$merchantAccountSampleXml}
        </api-error-response>
        ";
    }


    private static function _errorsSampleXml($errors)
    {
        return implode("\n", array_map("self::_errorSampleXml", $errors));
    }

    private static function _errorSampleXml($error)
    {
        return "
        <error>
            <attribute>{$error["attribute"]}</attribute>
            <code>{$error["code"]}</code>
            <message>{$error["message"]}</message>
        </error>
        ";
    }

    private static function _subscriptionSampleXml($data)
    {
        return "
        <subscription>
            <id>{$data["id"]}</id>
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
