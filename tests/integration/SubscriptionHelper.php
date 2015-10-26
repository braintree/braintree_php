<?php
namespace Test\Integration;

use Braintree;

class SubscriptionHelper
{
    public static function addOnDiscountPlan()
    {
        return array(
            'description' => "Plan for integration tests -- with add-ons and discounts",
            'id' => "integration_plan_with_add_ons_and_discounts",
            'price' => '9.99',
            'trial_period' => true,
            'trial_duration' => 2,
            'trial_duration_unit' => 'day'
        );
    }

    public static function billingDayOfMonthPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- with billing day of month',
            'id' => 'integration_plan_with_billing_day_of_month',
			'numberOfBillingCycles' => 5,
            'price' => '8.88',
            'trial_period' => false
        );
    }

    public static function trialPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- with trial',
            'id' => 'integration_trial_plan',
			'numberOfBillingCycles' => 12,
            'price' => '43.21',
            'trial_period' => true,
            'trial_duration' => 2,
            'trial_duration_unit' => 'day'
        );
    }

    public static function triallessPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- without a trial',
            'id' => 'integration_trialless_plan',
			'numberOfBillingCycles' => 12,
            'price' => '12.34',
            'trial_period' => false
        );
    }

    public static function createCreditCard()
    {
        $customer = Braintree\Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/2010'
            )
        ));
        return $customer->creditCards[0];
    }

    public static function createSubscription()
    {
        $plan = self::triallessPlan();
        $result = Braintree\Subscription::create(array(
            'paymentMethodToken' => self::createCreditCard()->token,
            'price' => '54.99',
            'planId' => $plan['id']
        ));
        return $result->subscription;
    }

    public static function compareModificationsById($left, $right)
    {
        return strcmp($left->id, $right->id);
    }

    public static function sortModificationsById(&$modifications)
    {
        usort($modifications, array('Test\Integration\SubscriptionHelper', 'compareModificationsById'));
    }
}
