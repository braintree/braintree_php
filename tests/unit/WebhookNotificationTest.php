<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_WebhookNotificationTest extends PHPUnit_Framework_TestCase
{
    function testVerify()
    {
        $verificationString = Braintree_WebhookNotification::verify('verification_token');
        $this->assertEquals('integration_public_key|1b0bcd1e59d260f28be85a6239c0dd01120ededc', $verificationString);
    }

    function testSampleNotificationReturnsAParsableNotification()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE, $webhookNotification->kind);
        $this->assertNotNull($webhookNotification->timestamp);
        $this->assertEquals("my_id", $webhookNotification->subscription->id);
    }

    function testParsingModifiedSignatureRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature');

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'] . "bad",
            $sampleNotification['payload']
        );
    }

    function testParsingUnknownPublicKeyRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature');

        $webhookNotification = Braintree_WebhookNotification::parse(
            "bad" . $sampleNotification['signature'],
            $sampleNotification['payload']
        );
    }

    function testParsingInvalidSignatureRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature');

        $webhookNotification = Braintree_WebhookNotification::parse(
            "bad_signature",
            $sampleNotification['payload']
        );
    }

    function testBuildsASampleNotificationForAMerchantAccountApprovedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->masterMerchantAccount->status);
    }

    function testBuildsASampleNotificationForAMerchantAccountDeclinedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->masterMerchantAccount->status);
        $this->assertEquals("Credit score is too low", $webhookNotification->message);
        $errors = $webhookNotification->errors->forKey('merchantAccount')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::MERCHANT_ACCOUNT_APPLICANT_DETAILS_DECLINED_OFAC, $errors[0]->code);
    }

    function testBuildsASampleNotificationForATransactionDisbursedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::TRANSACTION_DISBURSED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::TRANSACTION_DISBURSED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->transaction->id);
        $this->assertEquals(100, $webhookNotification->transaction->amount);
        $this->assertNotNull($webhookNotification->transaction->disbursementDetails->disbursementDate);
    }

    function testBuildsASampleNotificationForAPartnerMerchantConnectedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::PARTNER_MERCHANT_CONNECTED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::PARTNER_MERCHANT_CONNECTED, $webhookNotification->kind);
        $this->assertEquals("public_id", $webhookNotification->partnerMerchant->merchantPublicId);
        $this->assertEquals("public_key", $webhookNotification->partnerMerchant->publicKey);
        $this->assertEquals("private_key", $webhookNotification->partnerMerchant->privateKey);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
        $this->assertEquals("cse_key", $webhookNotification->partnerMerchant->clientSideEncryptionKey);
    }

    function testBuildsASampleNotificationForAPartnerMerchantDisconnectedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::PARTNER_MERCHANT_DISCONNECTED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::PARTNER_MERCHANT_DISCONNECTED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }

    function testBuildsASampleNotificationForAPartnerMerchantDeclinedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::PARTNER_MERCHANT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::PARTNER_MERCHANT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }
}
?>
