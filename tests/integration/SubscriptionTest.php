<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    function trialPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- with trial',
            'id' => 'integration_trial_plan',
            'price' => '43.21',
            'trial_period' => true,
            'trial_duration' => 2,
            'trial_duration_unit' => 'day' // Braintree::Subscription::TrialDurationUnit::Day
        );
    }

    function triallessPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- without a trial',
            'id' => 'integration_trialless_plan',
            'price' => '12.34',
            'trial_period' => false
        );
    }

    function createCreditCard()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/2010'
            )
        ));
        return $customer->creditCards[0];
    }

    function testCreate_whenSuccessful()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id']

        ));
        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($creditCard->token, $subscription->paymentMethodToken);
        $this->assertEquals(0, $subscription->failureCount);
        $this->assertEquals($plan['id'], $subscription->planId);
        $this->assertEquals('Active', $subscription->status);
        $this->assertType('DateTime', $subscription->firstBillingDate);
        $this->assertType('DateTime', $subscription->nextBillingDate);
        $this->assertType('DateTime', $subscription->billingPeriodStartDate);
        $this->assertType('DateTime', $subscription->billingPeriodEndDate);
    }

    function testCreate_canSetTheId()
    {
        $creditCard = $this->createCreditCard();
        $newId = strval(rand());
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'id' => $newId
        ));

        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($newId, $subscription->id);
    }

    function testCreate_trialPeriodDefaultsToPlanWithoutTrial()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertFalse($subscription->trialPeriod);
        $this->assertNull($subscription->trialDuration);
        $this->assertNull($subscription->trialDurationUnit);
    }

    function testCreate_trialPeriondDefaultsToPlanWithTrial()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->trialPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertTrue($subscription->trialPeriod);
        $this->assertEquals(2, $subscription->trialDuration);
        $this->assertEquals('day', $subscription->trialDurationUnit);
    }

    function testCreate_alterPlanTrialPeriod()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->trialPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'trialDuration' => 5,
            'trialDurationUnit' => 'month'
        ));
        $subscription = $result->subscription;
        $this->assertTrue($subscription->trialPeriod);
        $this->assertEquals(5, $subscription->trialDuration);
        $this->assertEquals('month', $subscription->trialDurationUnit);
    }

    function testCreate_removePlanTrialPeriod()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->trialPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'trialPeriod' => false,
        ));
        $subscription = $result->subscription;
        $this->assertFalse($subscription->trialPeriod);
    }

    function testCreate_createsATransactionIfNoTrialPeriod()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(1, sizeof($subscription->transactions));
        $transaction = $subscription->transactions[0];
        $this->assertType('Braintree_Transaction', $transaction);
        $this->assertEquals($plan['price'], $transaction->amount);
        $this->assertEquals('sale', $transaction->type);
    }

    function testCraete_doesNotCreateTransactionIfTrialPeriod()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->trialPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(0, sizeof($subscription->transactions));
    }

    function testCreat_priceCanBeOverriden()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->trialPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'price' => '2.00'
        ));
        $subscription = $result->subscription;
        $this->assertEquals('2.00', $subscription->price);
    }
}
?>

