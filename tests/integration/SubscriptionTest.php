<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class Braintree_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    function testCreate_doesNotAcceptBadAttributes()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: bad');
        $result = Braintree_Subscription::create(array(
            'bad' => 'value'
        ));
    }

    function testCreate_whenSuccessful()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id']

        ));
        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($creditCard->token, $subscription->paymentMethodToken);
        $this->assertEquals(0, $subscription->failureCount);
        $this->assertEquals($plan['id'], $subscription->planId);
        $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $subscription->merchantAccountId);
        $this->assertEquals('Active', $subscription->status);
        $this->assertType('DateTime', $subscription->firstBillingDate);
        $this->assertType('DateTime', $subscription->nextBillingDate);
        $this->assertType('DateTime', $subscription->billingPeriodStartDate);
        $this->assertType('DateTime', $subscription->billingPeriodEndDate);
    }

    function testCreate_canSetTheId()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $newId = strval(rand());
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
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
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId()
        ));

        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $subscription->merchantAccountId);
    }

    function testCreate_trialPeriodDefaultsToPlanWithoutTrial()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
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
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::trialPlan();
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
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::trialPlan();
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
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::trialPlan();
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
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(1, sizeof($subscription->transactions));
        $transaction = $subscription->transactions[0];
        $this->assertType('Braintree_Transaction', $transaction);
        $this->assertEquals($plan['price'], $transaction->amount);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals($subscription->id, $transaction->subscriptionId);
    }

    function testCreate_doesNotCreateTransactionIfTrialPeriod()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::trialPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(0, sizeof($subscription->transactions));
    }

    function testCreate_priceCanBeOverriden()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::trialPlan();
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
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'id' => 'invalid token'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('id');
        $this->assertEquals(Braintree_Error_Codes::SUBSCRIPTION_TOKEN_FORMAT_IS_INVALID, $errors[0]->code);
    }

    function testFind()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
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

    function testUpdate_whenSuccessful()
    {
        $subscription = Braintree_SubscriptionTestHelper::createSubscription();
        $newId = strval(rand());
        $newPlan = Braintree_SubscriptionTestHelper::trialPlan();
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
        $subscription = Braintree_SubscriptionTestHelper::createSubscription();
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
        $subscription = Braintree_SubscriptionTestHelper::createSubscription();
        $result = Braintree_Subscription::update($subscription->id, array('price' => ''));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('price');
        $this->assertEquals(Braintree_Error_Codes::SUBSCRIPTION_PRICE_CANNOT_BE_BLANK, $errors[0]->code);
    }

    function testUpdate_cannotUpdateCanceledSubscription()
    {
        $subscription = Braintree_SubscriptionTestHelper::createSubscription();
        Braintree_Subscription::cancel($subscription->id);
        $result = Braintree_Subscription::update($subscription->id, array('price' => '1.00'));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::SUBSCRIPTION_CANNOT_EDIT_CANCELED_SUBSCRIPTION, $errors[0]->code);
    }

    function testCancel_returnsSuccessIfCanceled()
    {
        $subscription = Braintree_SubscriptionTestHelper::createSubscription();
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
        $subscription = Braintree_SubscriptionTestHelper::createSubscription();
        Braintree_Subscription::cancel($subscription->id);
        $result = Braintree_Subscription::cancel($subscription->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('status');
        $this->assertEquals(Braintree_Error_Codes::SUBSCRIPTION_STATUS_IS_CANCELED, $errors[0]->code);
    }

    function testRetryCharge_WithoutAmount()
    {
        $subscription = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::ACTIVE))
        ))->firstItem();

        $result = Braintree_Subscription::retryCharge($subscription->id);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertEquals($subscription->price, $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
    }

    function testRetryCharge_WithAmount()
    {
        $subscription = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::ACTIVE))
        ))->firstItem();

        $result = Braintree_Subscription::retryCharge($subscription->id, 1000);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(1000, $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
    }
}
?>
