<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class Braintree_SubscriptionSearchTest extends PHPUnit_Framework_TestCase
{
    function testSearch_planIdIs()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id']
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->is("integration_trial_plan")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testSearch_planIdIsNot()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id']
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->isNot("integration_trialless_plan")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testSearch_planIdStartsWith()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id']
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->startsWith("integration_trial_pl")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testSearch_planIdEndsWith()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id']
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->endsWith("rial_plan")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }


    function testSearch_planIdContains()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id']
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->contains("ration_trial_pl")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testSearch_statusIn()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $activeSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $canceledSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;
        Braintree_Subscription::cancel($canceledSubscription->id);

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::ACTIVE))
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $activeSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $canceledSubscription));
    }

    function testSearch_statusIn_multipleValues()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $activeSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $canceledSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;
        Braintree_Subscription::cancel($canceledSubscription->id);

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::ACTIVE, Braintree_Subscription::CANCELED))
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $activeSubscription));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $canceledSubscription));
    }
}

?>
