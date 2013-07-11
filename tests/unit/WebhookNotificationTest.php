<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_WebhookNotificationTest extends PHPUnit_Framework_TestCase
{
    function testVerify()
    {
        $verificationString = Braintree_WebhookNotification::verify('verification_token');
        $this->assertEquals('integration_public_key|c9f15b74b0d98635cd182c51e2703cffa83388c3', $verificationString);
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
            Braintree_WebhookNotification::MERCHANT_ACCOUNT_APPROVED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::MERCHANT_ACCOUNT_APPROVED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->masterMerchantAccount->status);
    }

    function testBuildsASampleNotificationForAMerchantAccountDeclinedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::MERCHANT_ACCOUNT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::MERCHANT_ACCOUNT_DECLINED, $webhookNotification->kind);
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
}
?>
