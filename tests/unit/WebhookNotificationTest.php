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
}
?>
