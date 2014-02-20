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
        switch ($kind) {
            case Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED:
                $subjectXml = self::_merchantAccountApprovedSampleXml($id);
                break;
            case Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED:
                $subjectXml = self::_merchantAccountDeclinedSampleXml($id);
                break;
            case Braintree_WebhookNotification::TRANSACTION_DISBURSED:
                $subjectXml = self::_transactionDisbursedSampleXml($id);
                break;
            case Braintree_WebhookNotification::DISBURSEMENT_EXCEPTION:
                $subjectXml = self::_disbursementExceptionSampleXml($id);
                break;
            case Braintree_WebhookNotification::PARTNER_MERCHANT_CONNECTED:
                $subjectXml = self::_partnerMerchantConnectedSampleXml($id);
                break;
            case Braintree_WebhookNotification::PARTNER_MERCHANT_DISCONNECTED:
                $subjectXml = self::_partnerMerchantDisconnectedSampleXml($id);
                break;
            case Braintree_WebhookNotification::PARTNER_MERCHANT_DECLINED:
                $subjectXml = self::_partnerMerchantDeclinedSampleXml($id);
                break;
            default:
                $subjectXml = self::_subscriptionSampleXml($id);
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

    private static function _merchantAccountApprovedSampleXml($id)
    {
        return "
        <merchant_account>
            <id>{$id}</id>
            <master_merchant_account>
                <id>master_ma_for_{$id}</id>
                <status>active</status>
            </master_merchant_account>
            <status>active</status>
        </merchant_account>
        ";
    }

    private static function _merchantAccountDeclinedSampleXml($id)
    {
        return "
        <api-error-response>
            <message>Credit score is too low</message>
            <errors>
                <errors type=\"array\"/>
                    <merchant-account>
                        <errors type=\"array\">
                            <error>
                                <code>82621</code>
                                <message>Credit score is too low</message>
                                <attribute type=\"symbol\">base</attribute>
                            </error>
                        </errors>
                    </merchant-account>
                </errors>
                <merchant-account>
                    <id>{$id}</id>
                    <status>suspended</status>
                    <master-merchant-account>
                        <id>master_ma_for_{$id}</id>
                        <status>suspended</status>
                    </master-merchant-account>
                </merchant-account>
        </api-error-response>
        ";
    }

    private static function _transactionDisbursedSampleXml($id)
    {
        return "
        <transaction>
            <id>${id}</id>
            <amount>100</amount>
            <disbursement-details>
                <disbursement-date type=\"datetime\">2013-07-09T18:23:29Z</disbursement-date>
            </disbursement-details>
        </transaction>
        ";
    }

    private static function _disbursementExceptionSampleXml($id)
    {
        return "
        <disbursement-exception>
            <merchant-account-id>abcdef</merchant-account-id>
            <id>${id}</id>
            <message>invalid_account_number</message>
            <amount>100.00</amount>
            <disbursement-date>2014-02-10</disbursement-date>
            <follow-up-action>update</follow-up-action>
        </disbursement-exception>
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

    private static function _partnerMerchantConnectedSampleXml($id)
    {
        return "
        <partner_merchant>
          <merchant_public_id>public_id</merchant_public_id>
          <public_key>public_key</public_key>
          <private_key>private_key</private_key>
          <partner_merchant_id>abc123</partner_merchant_id>
          <client_side_encryption_key>cse_key</client_side_encryption_key>
        </partner_merchant>
        ";
    }

    private static function _partnerMerchantDisconnectedSampleXml($id)
    {
        return "
        <partner_merchant>
          <partner_merchant_id>abc123</partner_merchant_id>
        </partner_merchant>
        ";
    }

    private static function _partnerMerchantDeclinedSampleXml($id)
    {
        return "
        <partner_merchant>
          <partner_merchant_id>abc123</partner_merchant_id>
        </partner_merchant>
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
