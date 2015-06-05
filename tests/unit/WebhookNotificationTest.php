<?php namespace Braintree\Tests\Unit;

use Braintree\Configuration;
use Braintree\Dispute;
use Braintree\Error\Codes;
use Braintree\MerchantAccount;
use Braintree\WebhookNotification;
use Braintree\WebhookTesting;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class WebhookNotificationTest extends \PHPUnit_Framework_TestCase
{
    function setup()
    {
        integrationMerchantConfig();
    }

    function testVerify()
    {
        $verificationString = WebhookNotification::verify('20f9f8ed05f77439fe955c977e4c8a53');
        $this->assertEquals('integration_public_key|d9b899556c966b3f06945ec21311865d35df3ce4', $verificationString);
    }

    /**
     * @expectedException \Braintree\Exception\InvalidChallenge
     * @expectedExceptionMessage challenge contains non-hex characters
     */
    function testVerifyRaisesErrorWithInvalidChallenge()
    {
        WebhookNotification::verify('bad challenge');

    }

    function testSampleNotificationReturnsAParsableNotification()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE, $webhookNotification->kind);
        $this->assertNotNull($webhookNotification->timestamp);
        $this->assertEquals("my_id", $webhookNotification->subscription->id);
    }

    function testParsingModifiedSignatureRaisesError()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature',
            'signature does not match payload - one has been modified');

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'] . "bad",
            $sampleNotification['bt_payload']
        );
    }

    function testParsingWebhookWithWrongKeysRaisesError()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('wrong_public_key');
        Configuration::privateKey('wrong_private_key');

        $this->setExpectedException('Braintree\Exception\InvalidSignature', 'no matching public key');

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "bad" . $sampleNotification['bt_payload']
        );
    }

    function testParsingModifiedPayloadRaisesError()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature');

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "bad" . $sampleNotification['bt_payload']
        );
    }

    function testParsingUnknownPublicKeyRaisesError()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature');

        $webhookNotification = WebhookNotification::parse(
            "bad" . $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );
    }

    function testParsingInvalidSignatureRaisesError()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature');

        $webhookNotification = WebhookNotification::parse(
            "bad_signature",
            $sampleNotification['bt_payload']
        );
    }

    function testParsingInvalidCharactersRaisesError()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature', 'payload contains illegal characters');

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "~*~*invalid*~*~"
        );
    }

    function testParsingAllowsAllValidCharacters()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature',
            'signature does not match payload - one has been modified');

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+=/\n"
        );
    }

    function testParsingRetriesPayloadWithANewline()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            rtrim($sampleNotification['bt_payload'])
        );
    }

    function testBuildsASampleNotificationForAMerchantAccountApprovedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(MerchantAccount::STATUS_ACTIVE,
            $webhookNotification->merchantAccount->masterMerchantAccount->status);
    }

    function testBuildsASampleNotificationForAMerchantAccountDeclinedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(MerchantAccount::STATUS_SUSPENDED,
            $webhookNotification->merchantAccount->masterMerchantAccount->status);
        $this->assertEquals("Credit score is too low", $webhookNotification->message);
        $errors = $webhookNotification->errors->forKey('merchantAccount')->onAttribute('base');
        $this->assertEquals(Codes::MERCHANT_ACCOUNT_DECLINED_OFAC, $errors[0]->code);
    }

    function testBuildsASampleNotificationForATransactionDisbursedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::TRANSACTION_DISBURSED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::TRANSACTION_DISBURSED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->transaction->id);
        $this->assertEquals(100, $webhookNotification->transaction->amount);
        $this->assertNotNull($webhookNotification->transaction->disbursementDetails->disbursementDate);
    }

    function testBuildsASampleNotificationForADisputeOpenedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::DISPUTE_OPENED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::DISPUTE_OPENED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Dispute::OPEN, $webhookNotification->dispute->status);
    }

    function testBuildsASampleNotificationForADisputeLostWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::DISPUTE_LOST,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::DISPUTE_LOST, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Dispute::LOST, $webhookNotification->dispute->status);
    }

    function testBuildsASampleNotificationForADisputeWonWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::DISPUTE_WON,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::DISPUTE_WON, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Dispute::WON, $webhookNotification->dispute->status);
    }

    function testBuildsASampleNotificationForADisbursementExceptionWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::DISBURSEMENT_EXCEPTION,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );


        $this->assertEquals(WebhookNotification::DISBURSEMENT_EXCEPTION, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->disbursement->id);
        $this->assertEquals(false, $webhookNotification->disbursement->retry);
        $this->assertEquals(false, $webhookNotification->disbursement->success);
        $this->assertEquals("bank_rejected", $webhookNotification->disbursement->exceptionMessage);
        $this->assertEquals(100.00, $webhookNotification->disbursement->amount);
        $this->assertEquals("update_funding_information", $webhookNotification->disbursement->followUpAction);
        $this->assertEquals("merchant_account_token", $webhookNotification->disbursement->merchantAccount->id);
        $this->assertEquals(new \DateTime("2014-02-10"), $webhookNotification->disbursement->disbursementDate);
        $this->assertEquals(array("asdfg", "qwert"), $webhookNotification->disbursement->transactionIds);
    }

    function testBuildsASampleNotificationForADisbursementWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::DISBURSEMENT,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );


        $this->assertEquals(WebhookNotification::DISBURSEMENT, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->disbursement->id);
        $this->assertEquals(false, $webhookNotification->disbursement->retry);
        $this->assertEquals(true, $webhookNotification->disbursement->success);
        $this->assertEquals(null, $webhookNotification->disbursement->exceptionMessage);
        $this->assertEquals(100.00, $webhookNotification->disbursement->amount);
        $this->assertEquals(null, $webhookNotification->disbursement->followUpAction);
        $this->assertEquals("merchant_account_token", $webhookNotification->disbursement->merchantAccount->id);
        $this->assertEquals(new \DateTime("2014-02-10"), $webhookNotification->disbursement->disbursementDate);
        $this->assertEquals(array("asdfg", "qwert"), $webhookNotification->disbursement->transactionIds);
    }

    function testBuildsASampleNotificationForAPartnerMerchantConnectedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::PARTNER_MERCHANT_CONNECTED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::PARTNER_MERCHANT_CONNECTED, $webhookNotification->kind);
        $this->assertEquals("public_id", $webhookNotification->partnerMerchant->merchantPublicId);
        $this->assertEquals("public_key", $webhookNotification->partnerMerchant->publicKey);
        $this->assertEquals("private_key", $webhookNotification->partnerMerchant->privateKey);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
        $this->assertEquals("cse_key", $webhookNotification->partnerMerchant->clientSideEncryptionKey);
    }

    function testBuildsASampleNotificationForAPartnerMerchantDisconnectedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::PARTNER_MERCHANT_DISCONNECTED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::PARTNER_MERCHANT_DISCONNECTED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }

    function testBuildsASampleNotificationForAPartnerMerchantDeclinedWebhook()
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::PARTNER_MERCHANT_DECLINED,
            "my_id"
        );

        $webhookNotification = WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(WebhookNotification::PARTNER_MERCHANT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }
}
