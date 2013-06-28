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
            Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED, array(
                "id" => "sub_merchant_account_id",
                "status" => Braintree_MerchantAccount::STATUS_ACTIVE,
                "master_merchant_account" => array(
                    "id" => "master_merchant_account_id",
                    "status" => Braintree_MerchantAccount::STATUS_ACTIVE
                )
            )
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED, $webhookNotification->kind);
        $this->assertEquals("sub_merchant_account_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_merchant_account_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->masterMerchantAccount->status);
    }

    function testBuildsASampleNotificationForAMerchantAccountDeclinedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED, array(
                "message" => "Applicant declined due to OFAC.",
                "merchant_account" => array(
                    "id" => "sub_merchant_account_id",
                    "status" => Braintree_MerchantAccount::STATUS_SUSPENDED,
                    "master_merchant_account" => array(
                        "id" => "master_merchant_account_id",
                        "status" => Braintree_MerchantAccount::STATUS_ACTIVE
                    )
                ),
                "errors" => array(
                    array(
                        "attribute" => "base",
                        "code" => "82621",
                        "message" => "Applicant declined due to OFAC."
                    )
                )
            )
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['signature'],
            $sampleNotification['payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("sub_merchant_account_id", $webhookNotification->errors->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_SUSPENDED, $webhookNotification->errors->merchantAccount->status);
        $this->assertEquals("master_merchant_account_id", $webhookNotification->errors->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->errors->merchantAccount->masterMerchantAccount->status);
        $this->assertEquals("Applicant declined due to OFAC.", $webhookNotification->message);
        $errors = $webhookNotification->errors->errors->forKey('merchantAccount')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::MERCHANT_ACCOUNT_APPLICANT_DETAILS_DECLINED_OFAC, $errors[0]->code);
    }
}
?>
