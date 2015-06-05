<?php namespace Braintree\Tests\Integration;

use Braintree\Subscription;
use Braintree\SubscriptionSearch;
use Braintree\Tests\TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class MultipleValueNodeTest extends \PHPUnit_Framework_TestCase
{
    function testIn_singleValue()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $activeSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '3'
        ))->subscription;

        $canceledSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '3'
        ))->subscription;
        Subscription::cancel($canceledSubscription->id);

        $collection = Subscription::search(array(
            SubscriptionSearch::status()->in(array(Subscription::ACTIVE)),
            SubscriptionSearch::price()->is('3')
        ));
        foreach ($collection AS $item) {
            $this->assertEquals(Subscription::ACTIVE, $item->status);
        }

        $this->assertTrue(TestHelper::includes($collection, $activeSubscription));
        $this->assertFalse(TestHelper::includes($collection, $canceledSubscription));
    }

    function testIs()
    {
        $found = false;
        $collection = Subscription::search(array(
            SubscriptionSearch::status()->is(Subscription::PAST_DUE)
        ));
        foreach ($collection AS $item) {
            $found = true;
            $this->assertEquals(Subscription::PAST_DUE, $item->status);
        }
        $this->assertTrue($found);
    }

    function testSearch_statusIsExpired()
    {
        $found = false;
        $collection = Subscription::search(array(
            SubscriptionSearch::status()->in(array(Subscription::EXPIRED))
        ));
        foreach ($collection AS $item) {
            $found = true;
            $this->assertEquals(Subscription::EXPIRED, $item->status);
        }
        $this->assertTrue($found);
    }

    function testIn_multipleValues()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $activeSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '4'
        ))->subscription;

        $canceledSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '4'
        ))->subscription;
        Subscription::cancel($canceledSubscription->id);

        $collection = Subscription::search(array(
            SubscriptionSearch::status()->in(array(Subscription::ACTIVE, Subscription::CANCELED)),
            SubscriptionSearch::price()->is('4')
        ));

        $this->assertTrue(TestHelper::includes($collection, $activeSubscription));
        $this->assertTrue(TestHelper::includes($collection, $canceledSubscription));
    }
}
