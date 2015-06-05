<?php namespace Braintree\Tests\Integration;

use Braintree\Http;
use Braintree\Subscription;
use Braintree\SubscriptionSearch;
use TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class SubscriptionSearchTest extends \PHPUnit_Framework_TestCase
{
    function testSearch_planIdIs()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '1'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '1'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->is('integration_trial_plan'),
            SubscriptionSearch::price()->is('1')
        ));

        $this->assertTrue(TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(TestHelper::includes($collection, $triallessSubscription));
    }

    function test_noRequestsWhenIterating()
    {
        $resultsReturned = false;
        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->is('imaginary')
        ));

        foreach ($collection as $transaction) {
            $resultsReturned = true;
            break;
        }

        $this->assertSame(0, $collection->maximumCount());
        $this->assertEquals(false, $resultsReturned);
    }

    function testSearch_inTrialPeriod()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '1'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '1'
        ))->subscription;

        $subscriptions_in_trial = Subscription::search(array(
            SubscriptionSearch::inTrialPeriod()->is(true)
        ));

        $this->assertTrue(TestHelper::includes($subscriptions_in_trial, $trialSubscription));
        $this->assertFalse(TestHelper::includes($subscriptions_in_trial, $triallessSubscription));

        $subscriptions_not_in_trial = Subscription::search(array(
            SubscriptionSearch::inTrialPeriod()->is(false)
        ));

        $this->assertTrue(TestHelper::includes($subscriptions_not_in_trial, $triallessSubscription));
        $this->assertFalse(TestHelper::includes($subscriptions_not_in_trial, $trialSubscription));
    }

    function testSearch_statusIsPastDue()
    {
        $found = false;
        $collection = Subscription::search(array(
            SubscriptionSearch::status()->in(array(Subscription::PAST_DUE))
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

    function testSearch_billingCyclesRemaing()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $subscription_4 = Subscription::create(array(
            'paymentMethodToken'    => $creditCard->token,
            'planId'                => $triallessPlan['id'],
            'numberOfBillingCycles' => 4
        ))->subscription;

        $subscription_8 = Subscription::create(array(
            'paymentMethodToken'    => $creditCard->token,
            'planId'                => $triallessPlan['id'],
            'numberOfBillingCycles' => 8
        ))->subscription;

        $subscription_10 = Subscription::create(array(
            'paymentMethodToken'    => $creditCard->token,
            'planId'                => $triallessPlan['id'],
            'numberOfBillingCycles' => 10
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::billingCyclesRemaining()->between(5, 10)
        ));

        $this->assertFalse(TestHelper::includes($collection, $subscription_4));
        $this->assertTrue(TestHelper::includes($collection, $subscription_8));
        $this->assertTrue(TestHelper::includes($collection, $subscription_10));
    }

    function testSearch_subscriptionId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $rand_id = strval(rand());

        $subscription_1 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'id'                 => 'subscription_123_id_' . $rand_id
        ))->subscription;

        $subscription_2 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'id'                 => 'subscription_23_id_' . $rand_id
        ))->subscription;

        $subscription_3 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'id'                 => 'subscription_3_id_' . $rand_id
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::id()->contains("23_id_")
        ));

        $this->assertTrue(TestHelper::includes($collection, $subscription_1));
        $this->assertTrue(TestHelper::includes($collection, $subscription_2));
        $this->assertFalse(TestHelper::includes($collection, $subscription_3));
    }

    function testSearch_merchantAccountId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $rand_id = strval(rand());

        $subscription_1 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'id'                 => strval(rand()) . '_subscription_' . $rand_id,
            'price'              => '2'
        ))->subscription;

        $subscription_2 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'id'                 => strval(rand()) . '_subscription_' . $rand_id,
            'merchantAccountId'  => TestHelper::nonDefaultMerchantAccountId(),
            'price'              => '2'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::id()->endsWith('subscription_' . $rand_id),
            SubscriptionSearch::merchantAccountId()->in(array(TestHelper::nonDefaultMerchantAccountId())),
            SubscriptionSearch::price()->is('2')
        ));

        $this->assertFalse(TestHelper::includes($collection, $subscription_1));
        $this->assertTrue(TestHelper::includes($collection, $subscription_2));
    }

    function testSearch_bogusMerchantAccountId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $rand_id = strval(rand());

        $subscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'id'                 => strval(rand()) . '_subscription_' . $rand_id,
            'price'              => '11.38'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::id()->endsWith('subscription_' . $rand_id),
            SubscriptionSearch::merchantAccountId()->in(array("bogus_merchant_account")),
            SubscriptionSearch::price()->is('11.38')
        ));

        $this->assertFalse(TestHelper::includes($collection, $subscription));
    }

    function testSearch_daysPastDue()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $subscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id']
        ))->subscription;

        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . '/subscriptions/' . $subscription->id . '/make_past_due';
        $http->put($path, array('daysPastDue' => 5));

        $found = false;
        $collection = Subscription::search(array(
            SubscriptionSearch::daysPastDue()->between(2, 10)
        ));
        foreach ($collection AS $item) {
            $found = true;
            $this->assertTrue($item->daysPastDue <= 10);
            $this->assertTrue($item->daysPastDue >= 2);
        }
        $this->assertTrue($found);
    }

    function testSearch_price()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $subscription_850 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '8.50'
        ))->subscription;

        $subscription_851 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '8.51'
        ))->subscription;

        $subscription_852 = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '8.52'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::price()->between('8.51', '8.52')
        ));

        $this->assertTrue(TestHelper::includes($collection, $subscription_851));
        $this->assertTrue(TestHelper::includes($collection, $subscription_852));
        $this->assertFalse(TestHelper::includes($collection, $subscription_850));
    }

    function testSearch_nextBillingDate()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
        ))->subscription;

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
        ))->subscription;

        $fiveDaysFromNow = new \DateTime();
        $fiveDaysFromNow->modify("+5 days");

        $collection = Subscription::search(array(
            SubscriptionSearch::nextBillingDate()->greaterThanOrEqualTo($fiveDaysFromNow)
        ));

        $this->assertTrue(TestHelper::includes($collection, $triallessSubscription));
        $this->assertFalse(TestHelper::includes($collection, $trialSubscription));
    }

    function testSearch_transactionId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();

        $matchingSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
        ))->subscription;

        $nonMatchingSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::transactionId()->is($matchingSubscription->transactions[0]->id)
        ));

        $this->assertTrue(TestHelper::includes($collection, $matchingSubscription));
        $this->assertFalse(TestHelper::includes($collection, $nonMatchingSubscription));
    }
}
