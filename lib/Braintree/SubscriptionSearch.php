<?php

namespace Braintree;

class SubscriptionSearch
{
    static function billingCyclesRemaining()
    {
        return new RangeNode('billing_cycles_remaining');
    }

    static function daysPastDue()
    {
        return new RangeNode('days_past_due');
    }

    static function id()
    {
        return new TextNode('id');
    }

    static function inTrialPeriod()
    {
        return new MultipleValueNode('in_trial_period', array(true, false));
    }

    static function merchantAccountId()
    {
        return new MultipleValueNode('merchant_account_id');
    }

    static function nextBillingDate()
    {
        return new RangeNode('next_billing_date');
    }

    static function planId()
    {
        return new MultipleValueOrTextNode('plan_id');
    }

    static function price()
    {
        return new RangeNode('price');
    }

    static function status()
    {
        return new MultipleValueNode("status", array(
            Subscription::ACTIVE,
            Subscription::CANCELED,
            Subscription::EXPIRED,
            Subscription::PAST_DUE,
            Subscription::PENDING
        ));
    }

    static function transactionId()
    {
        return new TextNode('transaction_id');
    }

    static function ids()
    {
        return new MultipleValueNode('ids');
    }
}
