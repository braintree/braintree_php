<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class WebhookNotificationTest extends Setup
{
    public function setup()
    {
        self::integrationMerchantConfig();
    }

    public function testVerify()
    {
        $verificationString = Braintree\WebhookNotification::verify('20f9f8ed05f77439fe955c977e4c8a53');
        $this->assertEquals('integration_public_key|d9b899556c966b3f06945ec21311865d35df3ce4', $verificationString);
    }

    /**
     * @expectedException Braintree\Exception\InvalidChallenge
     * @expectedExceptionMessage challenge contains non-hex characters
     */
    public function testVerifyRaisesErrorWithInvalidChallenge()
    {
        $this->setExpectedException('Braintree\Exception\InvalidChallenge', 'challenge contains non-hex characters');

        Braintree\WebhookNotification::verify('bad challenge');
    }

    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage Braintree\Configuration::merchantId needs to be set (or accessToken needs to be passed to Braintree\Gateway)
     */
    public function testVerifyRaisesErrorWhenEnvironmentNotSet()
    {
        Braintree\Configuration::reset();

        Braintree\WebhookNotification::verify('20f9f8ed05f77439fe955c977e4c8a53');
    }

    public function testSampleNotificationReturnsAParsableNotification()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE, $webhookNotification->kind);
        $this->assertNotNull($webhookNotification->timestamp);
        $this->assertEquals("my_id", $webhookNotification->subscription->id);
    }

    public function testParsingModifiedSignatureRaisesError()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature', 'signature does not match payload - one has been modified');

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'] . "bad",
            $sampleNotification['bt_payload']
        );
    }

    /**
     * @expectedException Braintree\Exception\Configuration
     * @expectedExceptionMessage Braintree\Configuration::merchantId needs to be set (or accessToken needs to be passed to Braintree\Gateway)
     */
    public function testParsingWithNoKeysRaisesError()
    {
        Braintree\Configuration::reset();

        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );
    }

    public function testParsingWebhookWithWrongKeysRaisesError()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        Braintree\Configuration::environment('development');
        Braintree\Configuration::merchantId('integration_merchant_id');
        Braintree\Configuration::publicKey('wrong_public_key');
        Braintree\Configuration::privateKey('wrong_private_key');

        $this->setExpectedException('Braintree\Exception\InvalidSignature', 'no matching public key');

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "bad" . $sampleNotification['bt_payload']
        );
    }

    public function testParsingModifiedPayloadRaisesError()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature');

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "bad" . $sampleNotification['bt_payload']
        );
    }

    public function testParsingUnknownPublicKeyRaisesError()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature');

        $webhookNotification = Braintree\WebhookNotification::parse(
            "bad" . $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );
    }

    public function testParsingInvalidSignatureRaisesError()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature');

        $webhookNotification = Braintree\WebhookNotification::parse(
            "bad_signature",
            $sampleNotification['bt_payload']
        );
    }

    public function testParsingInvalidCharactersRaisesError()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature', 'payload contains illegal characters');

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "~*~*invalid*~*~"
        );
    }

    public function testParsingAllowsAllValidCharacters()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree\Exception\InvalidSignature', 'signature does not match payload - one has been modified');

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+=/\n"
        );
    }

    public function testParsingRetriesPayloadWithANewline()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            rtrim($sampleNotification['bt_payload'])
        );
    }

    public function testAllowsParsingUsingGateway()
    {
        Braintree\Configuration::reset();
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::CHECK,
            "my_id"
        );

        $gateway = new Braintree\Gateway([
            'privateKey' => 'integration_private_key',
            'publicKey' => 'integration_public_key',
            'merchantId' => 'integration_merchant_id',
            'environment' => 'development'
        ]);

        $webhookNotification = $gateway->webhookNotification()->parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::CHECK, $webhookNotification->kind);
    }

    public function testAllowsParsingUsingStaticMethods()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::CHECK,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::CHECK, $webhookNotification->kind);
    }

    public function testBuildsASampleNotificationForASubscriptionChargedSuccessfullyWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->subscription->id);
        $this->assertEquals(new DateTime('2016-03-21'), $webhookNotification->subscription->billingPeriodStartDate);
        $this->assertEquals(new DateTime('2017-03-31'), $webhookNotification->subscription->billingPeriodEndDate);
        $this->assertEquals(1, count($webhookNotification->subscription->transactions));

        $transaction = $webhookNotification->subscription->transactions[0];
        $this->assertEquals('submitted_for_settlement', $transaction->status);
        $this->assertEquals('49.99', $transaction->amount);
    }

    public function testBuildsASampleNotificationForAMerchantAccountApprovedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->masterMerchantAccount->status);
    }

    public function testBuildsASampleNotificationForAMerchantAccountDeclinedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->masterMerchantAccount->status);
        $this->assertEquals("Credit score is too low", $webhookNotification->message);
        $errors = $webhookNotification->errors->forKey('merchantAccount')->onAttribute('base');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_ACCOUNT_DECLINED_OFAC, $errors[0]->code);
    }

    public function testBuildsASampleNotificationForATransactionDisbursedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::TRANSACTION_DISBURSED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::TRANSACTION_DISBURSED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->transaction->id);
        $this->assertEquals(100, $webhookNotification->transaction->amount);
        $this->assertNotNull($webhookNotification->transaction->disbursementDetails->disbursementDate);
    }

    public function testBuildsASampleNotificationForATransactionSettledWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::TRANSACTION_SETTLED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::TRANSACTION_SETTLED, $webhookNotification->kind);
        $transaction = $webhookNotification->transaction;
        $this->assertEquals("my_id", $transaction->id);
        $this->assertEquals("settled", $transaction->status);
        $this->assertEquals(100, $transaction->amount);
        $this->assertEquals('123456789', $transaction->usBankAccount->routingNumber);
        $this->assertEquals('1234', $transaction->usBankAccount->last4);
        $this->assertEquals('checking', $transaction->usBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $transaction->usBankAccount->accountHolderName);
    }

    public function testBuildsASampleNotificationForATransactionSettlementDeclinedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED, $webhookNotification->kind);
        $transaction = $webhookNotification->transaction;
        $this->assertEquals("my_id", $transaction->id);
        $this->assertEquals("settlement_declined", $transaction->status);
        $this->assertEquals(100, $transaction->amount);
        $this->assertEquals('123456789', $transaction->usBankAccount->routingNumber);
        $this->assertEquals('1234', $transaction->usBankAccount->last4);
        $this->assertEquals('checking', $transaction->usBankAccount->accountType);
        $this->assertEquals('Dan Schulman', $transaction->usBankAccount->accountHolderName);
    }

    public function testBuildsASampleNotificationForADisputeOpenedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::DISPUTE_OPENED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::DISPUTE_OPENED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Braintree\Dispute::OPEN, $webhookNotification->dispute->status);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $webhookNotification->dispute->kind);
        $this->assertEquals(new DateTime('2014-03-21'), $webhookNotification->dispute->dateOpened);
    }

    public function testBuildsASampleNotificationForADisputeLostWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::DISPUTE_LOST,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::DISPUTE_LOST, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Braintree\Dispute::LOST, $webhookNotification->dispute->status);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $webhookNotification->dispute->kind);
        $this->assertEquals(new DateTime('2014-03-21'), $webhookNotification->dispute->dateOpened);
    }

    public function testBuildsASampleNotificationForADisputeWonWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::DISPUTE_WON,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::DISPUTE_WON, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Braintree\Dispute::WON, $webhookNotification->dispute->status);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $webhookNotification->dispute->kind);
        $this->assertEquals(new DateTime('2014-03-21'), $webhookNotification->dispute->dateOpened);
        $this->assertEquals(new DateTime('2014-03-22'), $webhookNotification->dispute->dateWon);
    }

    public function testBuildsASampleNotificationForADisbursementExceptionWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::DISBURSEMENT_EXCEPTION,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );


        $this->assertEquals(Braintree\WebhookNotification::DISBURSEMENT_EXCEPTION, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->disbursement->id);
        $this->assertEquals(false, $webhookNotification->disbursement->retry);
        $this->assertEquals(false, $webhookNotification->disbursement->success);
        $this->assertEquals("bank_rejected", $webhookNotification->disbursement->exceptionMessage);
        $this->assertEquals(100.00, $webhookNotification->disbursement->amount);
        $this->assertEquals("update_funding_information", $webhookNotification->disbursement->followUpAction);
        $this->assertEquals("merchant_account_token", $webhookNotification->disbursement->merchantAccount->id);
        $this->assertEquals(new DateTime("2014-02-10"), $webhookNotification->disbursement->disbursementDate);
        $this->assertEquals(["asdfg", "qwert"], $webhookNotification->disbursement->transactionIds);
    }

    public function testBuildsASampleNotificationForADisbursementWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::DISBURSEMENT,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );


        $this->assertEquals(Braintree\WebhookNotification::DISBURSEMENT, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->disbursement->id);
        $this->assertEquals(false, $webhookNotification->disbursement->retry);
        $this->assertEquals(true, $webhookNotification->disbursement->success);
        $this->assertEquals(NULL, $webhookNotification->disbursement->exceptionMessage);
        $this->assertEquals(100.00, $webhookNotification->disbursement->amount);
        $this->assertEquals(NULL, $webhookNotification->disbursement->followUpAction);
        $this->assertEquals("merchant_account_token", $webhookNotification->disbursement->merchantAccount->id);
        $this->assertEquals(new DateTime("2014-02-10"), $webhookNotification->disbursement->disbursementDate);
        $this->assertEquals(["asdfg", "qwert"], $webhookNotification->disbursement->transactionIds);
    }
    public function testBuildsASampleNotificationForAPartnerMerchantConnectedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::PARTNER_MERCHANT_CONNECTED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::PARTNER_MERCHANT_CONNECTED, $webhookNotification->kind);
        $this->assertEquals("public_id", $webhookNotification->partnerMerchant->merchantPublicId);
        $this->assertEquals("public_key", $webhookNotification->partnerMerchant->publicKey);
        $this->assertEquals("private_key", $webhookNotification->partnerMerchant->privateKey);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
        $this->assertEquals("cse_key", $webhookNotification->partnerMerchant->clientSideEncryptionKey);
    }

    public function testBuildsASampleNotificationForAPartnerMerchantDisconnectedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::PARTNER_MERCHANT_DISCONNECTED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::PARTNER_MERCHANT_DISCONNECTED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }

    public function testBuildsASampleNotificationForAPartnerMerchantDeclinedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::PARTNER_MERCHANT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::PARTNER_MERCHANT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }

    public function testBuildsASampleNotificationForConnectedMerchantStatusTransitionedWebhook()
    {
        Braintree\Configuration::reset();

        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::CONNECTED_MERCHANT_STATUS_TRANSITIONED,
            "my_id"
        );

        $gateway = new Braintree\Gateway([
            'privateKey' => 'integration_private_key',
            'publicKey' => 'integration_public_key',
            'merchantId' => 'integration_merchant_id',
            'environment' => 'development'
        ]);

        $webhookNotification = $gateway->webhookNotification()->parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::CONNECTED_MERCHANT_STATUS_TRANSITIONED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->connectedMerchantStatusTransitioned->merchantPublicId);
        $this->assertEquals("new_status", $webhookNotification->connectedMerchantStatusTransitioned->status);
        $this->assertEquals("oauth_application_client_id", $webhookNotification->connectedMerchantStatusTransitioned->oauthApplicationClientId);
    }

    public function testBuildsASampleNotificationForConnectedMerchantPayPalStatusChangedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::CONNECTED_MERCHANT_PAYPAL_STATUS_CHANGED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::CONNECTED_MERCHANT_PAYPAL_STATUS_CHANGED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->connectedMerchantPayPalStatusChanged->merchantPublicId);
        $this->assertEquals("link", $webhookNotification->connectedMerchantPayPalStatusChanged->action);
        $this->assertEquals("oauth_application_client_id", $webhookNotification->connectedMerchantPayPalStatusChanged->oauthApplicationClientId);
    }

    public function testBuildsASampleNotificationForACheckWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::CHECK,
            ""
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification["bt_signature"],
            $sampleNotification["bt_payload"]
        );

        $this->assertEquals(Braintree\WebhookNotification::CHECK, $webhookNotification->kind);
    }

    public function testAccountUpdaterDailyReportWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::ACCOUNT_UPDATER_DAILY_REPORT,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::ACCOUNT_UPDATER_DAILY_REPORT, $webhookNotification->kind);
        $this->assertEquals("link-to-csv-report", $webhookNotification->accountUpdaterDailyReport->reportUrl);
        $this->assertEquals(new DateTime("2016-01-14"), $webhookNotification->accountUpdaterDailyReport->reportDate);
    }

    public function testIdealPaymentCompleteWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::IDEAL_PAYMENT_COMPLETE,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::IDEAL_PAYMENT_COMPLETE, $webhookNotification->kind);
        $idealPayment = $webhookNotification->idealPayment;

        $this->assertEquals("my_id", $idealPayment->id);
        $this->assertEquals("COMPLETE", $idealPayment->status);
        $this->assertEquals("ORDERABC", $idealPayment->orderId);
        $this->assertEquals("10.00", $idealPayment->amount);
        $this->assertEquals("https://example.com", $idealPayment->approvalUrl);
        $this->assertEquals("1234567890", $idealPayment->idealTransactionId);
    }

    public function testIdealPaymentFailedWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::IDEAL_PAYMENT_FAILED,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::IDEAL_PAYMENT_FAILED, $webhookNotification->kind);
        $idealPayment = $webhookNotification->idealPayment;

        $this->assertEquals("my_id", $idealPayment->id);
        $this->assertEquals("FAILED", $idealPayment->status);
        $this->assertEquals("ORDERABC", $idealPayment->orderId);
        $this->assertEquals("10.00", $idealPayment->amount);
        $this->assertEquals("https://example.com", $idealPayment->approvalUrl);
        $this->assertEquals("1234567890", $idealPayment->idealTransactionId);
    }

    public function testGrantedPaymentInstrumentUpdateWebhook()
    {
        $sampleNotification = Braintree\WebhookTesting::sampleNotification(
            Braintree\WebhookNotification::GRANTED_PAYMENT_INSTRUMENT_UPDATE,
            "my_id"
        );

        $webhookNotification = Braintree\WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree\WebhookNotification::GRANTED_PAYMENT_INSTRUMENT_UPDATE, $webhookNotification->kind);
        $update = $webhookNotification->grantedPaymentInstrumentUpdate;

        $this->assertEquals("vczo7jqrpwrsi2px", $update->grantOwnerMerchantId);
        $this->assertEquals("cf0i8wgarszuy6hc", $update->grantRecipientMerchantId);
        $this->assertEquals("ee257d98-de40-47e8-96b3-a6954ea7a9a4", $update->paymentMethodNonce->nonce);
        $this->assertEquals("abc123z", $update->token);
        $this->assertEquals(array("expiration-month", "expiration-year"), $update->updatedFields);
    }
}
