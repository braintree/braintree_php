<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    function defaultMerchantAccountId()
    {
        return 'sandbox_credit_card';
    }

    function nonDefaultMerchantAccountId()
    {
        return 'sandbox_credit_card_non_default';
    }

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

    function createSubscription()
    {
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $this->createCreditCard()->token,
            'price' => '54.99',
            'planId' => $plan['id']
        ));
        return $result->subscription;
    }

    function testCreate_doesNotAcceptBadAttributes()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: bad');
        $result = Braintree_Subscription::create(array(
            'bad' => 'value'
        ));
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
        $this->assertEquals($this->defaultMerchantAccountId(), $subscription->merchantAccountId);
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

    function testCreate_canSetTheMerchantAccountId()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'merchantAccountId' => $this->nonDefaultMerchantAccountId()
        ));

        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($this->nonDefaultMerchantAccountId(), $subscription->merchantAccountId);
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

    function testCreate_doesNotCreateTransactionIfTrialPeriod()
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

    function testCreate_priceCanBeOverriden()
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

    function testValidationErrors_hasValidationErrorsOnId()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'id' => 'invalid token'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('id');
        $this->assertEquals('81906', $errors[0]->code);
    }

    function testFind()
    {
        $creditCard = $this->createCreditCard();
        $plan = $this->triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id']
        ));
        $this->assertTrue($result->success);
        $subscription = Braintree_Subscription::find($result->subscription->id);
        $this->assertEquals($result->subscription->id, $subscription->id);
        $this->assertEquals($plan['id'], $subscription->planId);
    }

    function testFind_throwsIfNotFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound', 'subscription with id does-not-exist not found');
        Braintree_Subscription::find('does-not-exist');

    }

    function testSearch_planIdIs()
    {
        $creditCard = $this->createCreditCard();
        $triallessPlan = $this->triallessPlan();
        $trialPlan = $this->trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id']
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_Search::planId()->is("integration_trial_plan")
        ));

        $this->assertTrue(Braintree_TestHelper::includesOnAnyPage($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includesOnAnyPage($collection, $triallessSubscription));
    }

    function testUpdate_whenSuccessful()
    {
        $subscription = $this->createSubscription();
        $newId = strval(rand());
        $newPlan = $this->trialPlan();
        $result = Braintree_Subscription::update($subscription->id, array(
            'id' => $newId,
            'price' => '999.99',
            'planId' => $newPlan['id']
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($newId, $result->subscription->id);
        $this->assertEquals($newPlan['id'], $result->subscription->planId);
        $this->assertEquals('999.99', $result->subscription->price);
    }

    function testUpdate_doesNotAcceptBadAttributes()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: bad');
        $result = Braintree_Subscription::update('id', array(
            'bad' => 'value'
        ));
    }

    function testUpdate_createsTransactionOnProration()
    {
        $subscription = $this->createSubscription();
        $result = Braintree_Subscription::update($subscription->id, array(
            'price' => $subscription->price + 1,
        ));
        $this->assertTrue($result->success);
        $this->assertEquals(sizeof($subscription->transactions) + 1, sizeof($result->subscription->transactions));
    }

    function testUpdate_invalidSubscriptionId()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_Subscription::update('does-not-exist', array());
    }

    function testUpdate_validationErrors()
    {
        $subscription = $this->createSubscription();
        $result = Braintree_Subscription::update($subscription->id, array('price' => ''));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('price');
        $this->assertEquals('81903', $errors[0]->code);
    }

    function testUpdate_cannotUpdateCanceledSubscription()
    {
        $subscription = $this->createSubscription();
        Braintree_Subscription::cancel($subscription->id);
        $result = Braintree_Subscription::update($subscription->id, array('price' => '1.00'));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('base');
        $this->assertEquals('81901', $errors[0]->code);
    }

    function testCancel_returnsSuccessIfCanceled()
    {
        $subscription = $this->createSubscription();
        $result = Braintree_Subscription::cancel($subscription->id);
        $this->assertTrue($result->success);
        $this->assertEquals('Canceled', $result->subscription->status);
    }

    function testCancel_throwsErrorIfRecordNotFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_Subscription::cancel('non-existing-id');
    }

    function testCancel_returnsErrorIfCancelingCanceledSubscription()
    {
        $subscription = $this->createSubscription();
        Braintree_Subscription::cancel($subscription->id);
        $result = Braintree_Subscription::cancel($subscription->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('status');
        $this->assertEquals('81905', $errors[0]->code);
    }
}
?>
