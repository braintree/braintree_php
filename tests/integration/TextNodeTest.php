<?php namespace Braintree\Tests\Integration;

use Braintree\Subscription;
use Braintree\SubscriptionSearch;
use TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class TextNodeTest extends \PHPUnit_Framework_TestCase
{
    function testIs()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '5'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '5'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->is("integration_trial_plan"),
            SubscriptionSearch::price()->is('5')
        ));

        $this->assertTrue(TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(TestHelper::includes($collection, $triallessSubscription));
    }

    function testIsNot()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '6'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '6'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->isNot("integration_trialless_plan"),
            SubscriptionSearch::price()->is("6")
        ));

        $this->assertTrue(TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(TestHelper::includes($collection, $triallessSubscription));
    }

    function testStartsWith()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '7'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '7'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->startsWith("integration_trial_pl"),
            SubscriptionSearch::price()->is("7")
        ));

        $this->assertTrue(TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(TestHelper::includes($collection, $triallessSubscription));
    }

    function testEndsWith()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '8'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '8'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->endsWith("rial_plan"),
            SubscriptionSearch::price()->is("8")
        ));

        $this->assertTrue(TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(TestHelper::includes($collection, $triallessSubscription));
    }


    function testContains()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $triallessPlan = SubscriptionTestHelper::triallessPlan();
        $trialPlan = SubscriptionTestHelper::trialPlan();

        $trialSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $trialPlan['id'],
            'price'              => '9'
        ))->subscription;

        $triallessSubscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $triallessPlan['id'],
            'price'              => '9'
        ))->subscription;

        $collection = Subscription::search(array(
            SubscriptionSearch::planId()->contains("ration_trial_pl"),
            SubscriptionSearch::price()->is("9")
        ));

        $this->assertTrue(TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(TestHelper::includes($collection, $triallessSubscription));
    }
}
