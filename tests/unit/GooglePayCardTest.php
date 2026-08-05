<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class GooglePayCardTest extends Setup
{
    public function testFactory_mapsVirtualCardAttributes()
    {
        $card = Braintree\GooglePayCard::factory([
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
        ]);

        $this->assertEquals('1234', $card->last4);
        $this->assertEquals('Visa', $card->cardType);
    }

    public function testFactory_defaultsExpirationFields()
    {
        $card = Braintree\GooglePayCard::factory([
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
        ]);

        $this->assertEquals('', $card->expirationMonth);
        $this->assertEquals('', $card->expirationYear);
    }

    public function testFactory_keepsProvidedAttributes()
    {
        $card = Braintree\GooglePayCard::factory([
            'token' => 'google_pay_token',
            'sourceCardLast4' => '5678',
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
        ]);

        $this->assertEquals('google_pay_token', $card->token);
        $this->assertEquals('5678', $card->sourceCardLast4);
    }

    public function testIsDefault_returnsTrueWhenDefault()
    {
        $card = Braintree\GooglePayCard::factory([
            'default' => true,
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
        ]);

        $this->assertTrue($card->isDefault());
    }

    public function testIsDefault_returnsFalseWhenNotDefault()
    {
        $card = Braintree\GooglePayCard::factory([
            'default' => false,
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
        ]);

        $this->assertFalse($card->isDefault());
    }

    public function testFactory_buildsSubscriptionObjects()
    {
        $card = Braintree\GooglePayCard::factory([
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
            'subscriptions' => [
                ['id' => 'subscription_1'],
                ['id' => 'subscription_2'],
            ],
        ]);

        $this->assertCount(2, $card->subscriptions);
        $this->assertInstanceOf('Braintree\Subscription', $card->subscriptions[0]);
        $this->assertEquals('subscription_1', $card->subscriptions[0]->id);
    }

    public function testFactory_subscriptionsDefaultToEmptyArray()
    {
        $card = Braintree\GooglePayCard::factory([
            'virtualCardLast4' => '1234',
            'virtualCardType' => 'Visa',
        ]);

        $this->assertEquals([], $card->subscriptions);
    }
}
