<?php namespace Braintree\Tests\Integration;

use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\Http;
use Braintree\PaymentMethod;
use Braintree\Subscription;
use Braintree\Test\Nonces;
use Braintree\Transaction;
use Braintree\Tests\TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    function testCreate_doesNotAcceptBadAttributes()
    {
        $this->setExpectedException('\InvalidArgumentException', 'invalid keys: bad');
        $result = Subscription::create(array(
            'bad' => 'value'
        ));
    }

    function testCreate_whenSuccessful()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']

        ));
        TestHelper::assertPrintable($result);
        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($creditCard->token, $subscription->paymentMethodToken);
        $this->assertEquals(0, $subscription->failureCount);
        $this->assertEquals($plan['id'], $subscription->planId);
        $this->assertEquals(TestHelper::defaultMerchantAccountId(), $subscription->merchantAccountId);
        $this->assertEquals(Subscription::ACTIVE, $subscription->status);
        $this->assertEquals('12.34', $subscription->nextBillAmount);
        $this->assertEquals('12.34', $subscription->nextBillingPeriodAmount);
        $this->assertEquals('0.00', $subscription->balance);
        $this->assertEquals(1, $subscription->currentBillingCycle);
        $this->assertInstanceOf('\DateTime', $subscription->firstBillingDate);
        $this->assertInstanceOf('\DateTime', $subscription->nextBillingDate);
        $this->assertInstanceOf('\DateTime', $subscription->billingPeriodStartDate);
        $this->assertInstanceOf('\DateTime', $subscription->billingPeriodEndDate);
        $this->assertInstanceOf('\DateTime', $subscription->paidThroughDate);
        $this->assertInstanceOf('\DateTime', $subscription->updatedAt);
        $this->assertInstanceOf('\DateTime', $subscription->createdAt);

        $this->assertEquals('12.34', $subscription->statusHistory[0]->price);
        $this->assertEquals('0.00', $subscription->statusHistory[0]->balance);
        $this->assertEquals(Subscription::ACTIVE, $subscription->statusHistory[0]->status);
        $this->assertEquals(Subscription::API, $subscription->statusHistory[0]->subscriptionSource);
    }

    function testGatewayCreate_whenSuccessful()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $result = $gateway->subscription()->create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']

        ));
        TestHelper::assertPrintable($result);
        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($creditCard->token, $subscription->paymentMethodToken);
        $this->assertEquals(0, $subscription->failureCount);
        $this->assertEquals($plan['id'], $subscription->planId);
        $this->assertEquals(TestHelper::defaultMerchantAccountId(), $subscription->merchantAccountId);
        $this->assertEquals(Subscription::ACTIVE, $subscription->status);
    }

    function testCreate_withPaymentMethodNonce()
    {
        $customerId = Customer::create()->customer->id;
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "customerId" => $customerId,
            "share"      => true
        ));
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodNonce' => $nonce,
            'planId'             => $plan['id']
        ));

        $this->assertTrue($result->success);

        $transaction = $result->subscription->transactions[0];
        $this->assertEquals("411111", $transaction->creditCardDetails->bin);
        $this->assertEquals("1111", $transaction->creditCardDetails->last4);
    }

    function testCreate_returnsTransactionWhenTransactionFails()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'price'              => TransactionAmounts::$decline

        ));
        TestHelper::assertPrintable($result);
        $this->assertFalse($result->success);
        $this->assertEquals(Transaction::PROCESSOR_DECLINED, $result->transaction->status);
    }

    function testCreate_canSetTheId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $newId = strval(rand());
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'id'                 => $newId
        ));

        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals($newId, $subscription->id);
    }

    function testCreate_canSetTheMerchantAccountId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'merchantAccountId'  => TestHelper::nonDefaultMerchantAccountId()
        ));

        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals(TestHelper::nonDefaultMerchantAccountId(), $subscription->merchantAccountId);
    }

    function testCreate_trialPeriodDefaultsToPlanWithoutTrial()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertFalse($subscription->trialPeriod);
        $this->assertNull($subscription->trialDuration);
        $this->assertNull($subscription->trialDurationUnit);
    }

    function testCreate_trialPeriondDefaultsToPlanWithTrial()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertTrue($subscription->trialPeriod);
        $this->assertEquals(2, $subscription->trialDuration);
        $this->assertEquals('day', $subscription->trialDurationUnit);
    }

    function testCreate_alterPlanTrialPeriod()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'trialDuration'      => 5,
            'trialDurationUnit'  => 'month'
        ));
        $subscription = $result->subscription;
        $this->assertTrue($subscription->trialPeriod);
        $this->assertEquals(5, $subscription->trialDuration);
        $this->assertEquals('month', $subscription->trialDurationUnit);
    }

    function testCreate_removePlanTrialPeriod()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'trialPeriod'        => false,
        ));
        $subscription = $result->subscription;
        $this->assertFalse($subscription->trialPeriod);
    }

    function testCreate_createsATransactionIfNoTrialPeriod()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(1, sizeof($subscription->transactions));
        $transaction = $subscription->transactions[0];
        $this->assertInstanceOf('Transaction', $transaction);
        $this->assertEquals($plan['price'], $transaction->amount);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals($subscription->id, $transaction->subscriptionId);
    }

    function testCreate_doesNotCreateTransactionIfTrialPeriod()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(0, sizeof($subscription->transactions));
    }

    function testCreate_returnsATransactionWithSubscriptionBillingPeriod()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
        ));
        $subscription = $result->subscription;
        $transaction = $subscription->transactions[0];
        $this->assertEquals($subscription->billingPeriodStartDate,
            $transaction->subscriptionDetails->billingPeriodStartDate);
        $this->assertEquals($subscription->billingPeriodEndDate,
            $transaction->subscriptionDetails->billingPeriodEndDate);
    }

    function testCreate_priceCanBeOverriden()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'price'              => '2.00'
        ));
        $subscription = $result->subscription;
        $this->assertEquals('2.00', $subscription->price);
    }

    function testCreate_billingDayOfMonthIsInheritedFromPlan()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::billingDayOfMonthPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']
        ));
        $subscription = $result->subscription;
        $this->assertEquals(5, $subscription->billingDayOfMonth);
    }

    function testCreate_billingDayOfMonthCanBeOverriden()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::billingDayOfMonthPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'billingDayOfMonth'  => 14
        ));
        $subscription = $result->subscription;
        $this->assertEquals(14, $subscription->billingDayOfMonth);
    }

    function testCreate_billingDayOfMonthCanBeOverridenWithStartImmediately()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::billingDayOfMonthPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'options'            => array('startImmediately' => true)
        ));
        $subscription = $result->subscription;
        $this->assertEquals(1, sizeof($subscription->transactions));
    }

    function testCreate_firstBillingDateCanBeSet()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::billingDayOfMonthPlan();

        $tomorrow = new \DateTime("now + 1 day");
        $tomorrow->setTime(0, 0, 0);

        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'firstBillingDate'   => $tomorrow
        ));

        $subscription = $result->subscription;
        $this->assertEquals($tomorrow, $subscription->firstBillingDate);
        $this->assertEquals(Subscription::PENDING, $result->subscription->status);
    }

    function testCreate_firstBillingDateInThePast()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::billingDayOfMonthPlan();

        $past = new \DateTime("now - 3 days");
        $past->setTime(0, 0, 0);

        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'firstBillingDate'   => $past
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('firstBillingDate');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_FIRST_BILLING_DATE_CANNOT_BE_IN_THE_PAST, $errors[0]->code);
    }

    function testCreate_numberOfBillingCyclesCanBeOverridden()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']
        ));
        $subscription = $result->subscription;
        $this->assertEquals($plan['numberOfBillingCycles'], $subscription->numberOfBillingCycles);

        $result = Subscription::create(array(
            'numberOfBillingCycles' => '10',
            'paymentMethodToken'    => $creditCard->token,
            'planId'                => $plan['id']
        ));
        $subscription = $result->subscription;
        $this->assertEquals(10, $subscription->numberOfBillingCycles);
        $this->assertFalse($subscription->neverExpires);
    }

    function testCreate_numberOfBillingCyclesCanBeOverriddenToNeverExpire()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']
        ));
        $subscription = $result->subscription;
        $this->assertEquals($plan['numberOfBillingCycles'], $subscription->numberOfBillingCycles);

        $result = Subscription::create(array(
            'neverExpires'       => true,
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']
        ));
        $subscription = $result->subscription;
        $this->assertNull($subscription->numberOfBillingCycles);
        $this->assertTrue($subscription->neverExpires);
    }

    function testCreate_doesNotInheritAddOnsAndDiscountsWhenDoNotInheritAddOnsOrDiscountsIsSet()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'options'            => array('doNotInheritAddOnsOrDiscounts' => true)
        ));
        $subscription = $result->subscription;
        $this->assertEquals(0, sizeof($subscription->addOns));
        $this->assertEquals(0, sizeof($subscription->discounts));
    }

    function testCreate_inheritsAddOnsAndDiscountsFromPlanByDefault()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
        ));
        $subscription = $result->subscription;
        $this->assertEquals(2, sizeof($subscription->addOns));
        $addOns = $subscription->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->amount, "10.00");
        $this->assertEquals($addOns[0]->quantity, 1);
        $this->assertEquals($addOns[0]->numberOfBillingCycles, null);
        $this->assertEquals($addOns[0]->neverExpires, true);
        $this->assertEquals($addOns[0]->currentBillingCycle, 0);

        $this->assertEquals($addOns[1]->amount, "20.00");
        $this->assertEquals($addOns[1]->quantity, 1);
        $this->assertEquals($addOns[1]->numberOfBillingCycles, null);
        $this->assertEquals($addOns[1]->neverExpires, true);
        $this->assertEquals($addOns[1]->currentBillingCycle, 0);

        $this->assertEquals(2, sizeof($subscription->discounts));
        $discounts = $subscription->discounts;
        SubscriptionTestHelper::sortModificationsById($discounts);

        $this->assertEquals($discounts[0]->amount, "11.00");
        $this->assertEquals($discounts[0]->quantity, 1);
        $this->assertEquals($discounts[0]->numberOfBillingCycles, null);
        $this->assertEquals($discounts[0]->neverExpires, true);
        $this->assertEquals($discounts[0]->currentBillingCycle, 0);

        $this->assertEquals($discounts[1]->amount, "7.00");
        $this->assertEquals($discounts[1]->quantity, 1);
        $this->assertEquals($discounts[1]->numberOfBillingCycles, null);
        $this->assertEquals($discounts[1]->neverExpires, true);
        $this->assertEquals($discounts[1]->currentBillingCycle, 0);
    }

    function testCreate_allowsOverridingInheritedAddOnsAndDiscounts()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'addOns'             => array(
                'update' => array(
                    array(
                        'amount'                => '50.00',
                        'existingId'            => 'increase_10',
                        'quantity'              => 2,
                        'numberOfBillingCycles' => 5
                    ),
                    array(
                        'amount'                => '60.00',
                        'existingId'            => 'increase_20',
                        'quantity'              => 4,
                        'numberOfBillingCycles' => 9
                    )
                ),
            ),
            'discounts'          => array(
                'update' => array(
                    array(
                        'amount'       => '15.00',
                        'existingId'   => 'discount_7',
                        'quantity'     => 2,
                        'neverExpires' => true
                    )
                )
            )
        ));
        $subscription = $result->subscription;
        $this->assertEquals(2, sizeof($subscription->addOns));
        $addOns = $subscription->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->amount, "50.00");
        $this->assertEquals($addOns[0]->quantity, 2);
        $this->assertEquals($addOns[0]->numberOfBillingCycles, 5);
        $this->assertEquals($addOns[0]->neverExpires, false);
        $this->assertEquals($addOns[0]->currentBillingCycle, 0);

        $this->assertEquals($addOns[1]->amount, "60.00");
        $this->assertEquals($addOns[1]->quantity, 4);
        $this->assertEquals($addOns[1]->numberOfBillingCycles, 9);
        $this->assertEquals($addOns[1]->neverExpires, false);
        $this->assertEquals($addOns[1]->currentBillingCycle, 0);

        $this->assertEquals(2, sizeof($subscription->discounts));
        $discounts = $subscription->discounts;
        SubscriptionTestHelper::sortModificationsById($discounts);

        $this->assertEquals($discounts[0]->amount, "11.00");
        $this->assertEquals($discounts[0]->quantity, 1);
        $this->assertEquals($discounts[0]->numberOfBillingCycles, null);
        $this->assertEquals($discounts[0]->neverExpires, true);
        $this->assertEquals($discounts[0]->currentBillingCycle, 0);

        $this->assertEquals($discounts[1]->amount, "15.00");
        $this->assertEquals($discounts[1]->quantity, 2);
        $this->assertEquals($discounts[1]->numberOfBillingCycles, null);
        $this->assertEquals($discounts[1]->neverExpires, true);
        $this->assertEquals($discounts[1]->currentBillingCycle, 0);
    }

    function testCreate_allowsRemovalOfInheritedAddOnsAndDiscounts()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'addOns'             => array(
                'remove' => array('increase_10', 'increase_20')
            ),
            'discounts'          => array(
                'remove' => array('discount_7')
            )
        ));
        $subscription = $result->subscription;
        $this->assertEquals(0, sizeof($subscription->addOns));

        $this->assertEquals(1, sizeof($subscription->discounts));

        $this->assertEquals($subscription->discounts[0]->amount, "11.00");
        $this->assertEquals($subscription->discounts[0]->quantity, 1);
        $this->assertEquals($subscription->discounts[0]->numberOfBillingCycles, null);
        $this->assertEquals($subscription->discounts[0]->neverExpires, true);
        $this->assertEquals($subscription->discounts[0]->currentBillingCycle, 0);
    }

    function testCreate_allowsAddingNewAddOnsAndDiscounts()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'addOns'             => array(
                'add' => array(
                    array(
                        'inheritedFromId' => 'increase_30',
                        'amount'          => '35.00',
                        'neverExpires'    => true,
                        'quantity'        => 2
                    ),
                ),
            ),
            'discounts'          => array(
                'add' => array(
                    array(
                        'inheritedFromId'       => 'discount_15',
                        'amount'                => '15.50',
                        'numberOfBillingCycles' => 10,
                        'quantity'              => 3
                    )
                )
            )
        ));

        $subscription = $result->subscription;
        $this->assertEquals(3, sizeof($subscription->addOns));
        $addOns = $subscription->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->amount, "10.00");
        $this->assertEquals($addOns[1]->amount, "20.00");
        $this->assertEquals($addOns[2]->id, "increase_30");
        $this->assertEquals($addOns[2]->amount, "35.00");
        $this->assertEquals($addOns[2]->neverExpires, true);
        $this->assertEquals($addOns[2]->numberOfBillingCycles, null);
        $this->assertEquals($addOns[2]->quantity, 2);
        $this->assertEquals($addOns[2]->currentBillingCycle, 0);


        $this->assertEquals(3, sizeof($subscription->discounts));
        $discounts = $subscription->discounts;
        SubscriptionTestHelper::sortModificationsById($discounts);

        $this->assertEquals($discounts[0]->amount, "11.00");

        $this->assertEquals($discounts[1]->amount, "15.50");
        $this->assertEquals($discounts[1]->id, "discount_15");
        $this->assertEquals($discounts[1]->neverExpires, false);
        $this->assertEquals($discounts[1]->numberOfBillingCycles, 10);
        $this->assertEquals($discounts[1]->quantity, 3);
        $this->assertEquals($discounts[1]->currentBillingCycle, 0);

        $this->assertEquals($discounts[2]->amount, "7.00");
    }

    function testCreate_properlyParsesValidationErrorsForArrays()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'addOns'             => array(
                'update' => array(
                    array(
                        'existingId' => 'increase_10',
                        'amount'     => 'invalid',
                    ),
                    array(
                        'existingId' => 'increase_20',
                        'quantity'   => -10,
                    )
                )
            )
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->forKey('addOns')->forKey('update')->forIndex(0)->onAttribute('amount');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_MODIFICATION_AMOUNT_IS_INVALID, $errors[0]->code);
        $errors = $result->errors->forKey('subscription')->forKey('addOns')->forKey('update')->forIndex(1)->onAttribute('quantity');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_MODIFICATION_QUANTITY_IS_INVALID, $errors[0]->code);
    }

    function testCreate_withDescriptor()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'descriptor'         => array(
                'name'  => '123*123456789012345678',
                'phone' => '3334445555',
                'url'   => 'ebay.com'
            )
        ));
        $this->assertTrue($result->success);
        $subscription = $result->subscription;
        $this->assertEquals('123*123456789012345678', $subscription->descriptor->name);
        $this->assertEquals('3334445555', $subscription->descriptor->phone);
        $this->assertEquals('ebay.com', $subscription->descriptor->url);
        $transaction = $subscription->transactions[0];
        $this->assertEquals('123*123456789012345678', $transaction->descriptor->name);
        $this->assertEquals('3334445555', $transaction->descriptor->phone);
        $this->assertEquals('ebay.com', $transaction->descriptor->url);
    }

    function testCreate_withDescriptorValidation()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'descriptor'         => array(
                'name'  => 'xxxxxx',
                'phone' => 'xxxx',
                'url'   => '12345678901234'
            )
        ));
        $this->assertFalse($result->success);
        $subscription = $result->subscription;

        $errors = $result->errors->forKey('subscription')->forKey('descriptor')->onAttribute('name');
        $this->assertEquals(Error_Codes::DESCRIPTOR_NAME_FORMAT_IS_INVALID, $errors[0]->code);

        $errors = $result->errors->forKey('subscription')->forKey('descriptor')->onAttribute('phone');
        $this->assertEquals(Error_Codes::DESCRIPTOR_PHONE_FORMAT_IS_INVALID, $errors[0]->code);

        $errors = $result->errors->forKey('subscription')->forKey('descriptor')->onAttribute('url');
        $this->assertEquals(Error_Codes::DESCRIPTOR_URL_FORMAT_IS_INVALID, $errors[0]->code);
    }

    function testCreate_fromPayPalACcount()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $plan = SubscriptionTestHelper::triallessPlan();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $paypalResult = PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $subscriptionResult = Subscription::create(array(
            'paymentMethodToken' => $paymentMethodToken,
            'planId'             => $plan['id']

        ));
        $this->assertTrue($subscriptionResult->success);
        $transaction = $subscriptionResult->subscription->transactions[0];
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
    }

    function testCreate_fromPayPalACcountDoesNotWorkWithFutureNonce()
    {
        $plan = SubscriptionTestHelper::triallessPlan();
        $nonce = Nonces::$paypalFuturePayment;

        $subscriptionResult = Subscription::create(array(
            'paymentMethodNonce' => $nonce,
            'planId'             => $plan['id']

        ));
        $this->assertFalse($subscriptionResult->success);
        $errors = $subscriptionResult->errors->forKey('subscription')->errors;
        $this->assertEquals(Error_Codes::SUBSCRIPTION_PAYMENT_METHOD_NONCE_IS_INVALID, $errors[0]->code);
    }

    function testCreate_fromPayPalACcountDoesNotWorkWithOnetimeNonce()
    {
        $plan = SubscriptionTestHelper::triallessPlan();
        $nonce = Nonces::$paypalOneTimePayment;

        $subscriptionResult = Subscription::create(array(
            'paymentMethodNonce' => $nonce,
            'planId'             => $plan['id']

        ));
        $this->assertFalse($subscriptionResult->success);
        $errors = $subscriptionResult->errors->forKey('subscription')->errors;
        $this->assertEquals(Error_Codes::SUBSCRIPTION_PAYMENT_METHOD_NONCE_IS_INVALID, $errors[0]->code);
    }

    function testValidationErrors_hasValidationErrorsOnId()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'id'                 => 'invalid token'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('id');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_TOKEN_FORMAT_IS_INVALID, $errors[0]->code);
    }

    function testFind()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id']
        ));
        $this->assertTrue($result->success);
        $subscription = Subscription::find($result->subscription->id);
        $this->assertEquals($result->subscription->id, $subscription->id);
        $this->assertEquals($plan['id'], $subscription->planId);
    }

    function testFind_throwsIfNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'subscription with id does-not-exist not found');
        Subscription::find('does-not-exist');

    }

    function testUpdate_whenSuccessful()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $newId = strval(rand());
        $newPlan = SubscriptionTestHelper::trialPlan();
        $result = Subscription::update($subscription->id, array(
            'id'     => $newId,
            'price'  => '999.99',
            'planId' => $newPlan['id']
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($newId, $result->subscription->id);
        $this->assertEquals($newPlan['id'], $result->subscription->planId);
        $this->assertEquals('999.99', $result->subscription->price);
    }

    function testUpdate_doesNotAcceptBadAttributes()
    {
        $this->setExpectedException('\InvalidArgumentException', 'invalid keys: bad');
        $result = Subscription::update('id', array(
            'bad' => 'value'
        ));
    }

    function testUpdate_canUpdateNumberOfBillingCycles()
    {
        $plan = SubscriptionTestHelper::triallessPlan();
        $subscription = SubscriptionTestHelper::createSubscription();
        $this->assertEquals($plan['numberOfBillingCycles'], $subscription->numberOfBillingCycles);

        $updatedSubscription = Subscription::update($subscription->id, array(
            'numberOfBillingCycles' => 15
        ))->subscription;
        $this->assertEquals(15, $updatedSubscription->numberOfBillingCycles);
    }

    function testUpdate_canUpdateNumberOfBillingCyclesToNeverExpire()
    {
        $plan = SubscriptionTestHelper::triallessPlan();
        $subscription = SubscriptionTestHelper::createSubscription();
        $this->assertEquals($plan['numberOfBillingCycles'], $subscription->numberOfBillingCycles);

        $updatedSubscription = Subscription::update($subscription->id, array(
            'neverExpires' => true
        ))->subscription;
        $this->assertNull($updatedSubscription->numberOfBillingCycles);
    }

    function testUpdate_createsTransactionOnProration()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::update($subscription->id, array(
            'price' => $subscription->price + 1,
        ));
        $this->assertTrue($result->success);
        $this->assertEquals(sizeof($subscription->transactions) + 1, sizeof($result->subscription->transactions));
    }

    function testUpdate_createsProratedTransactionWhenFlagIsPassedTrue()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::update($subscription->id, array(
            'price'   => $subscription->price + 1,
            'options' => array('prorateCharges' => true)
        ));
        $this->assertTrue($result->success);
        $this->assertEquals(sizeof($subscription->transactions) + 1, sizeof($result->subscription->transactions));
    }

    function testUpdate_createsProratedTransactionWhenFlagIsPassedFalse()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::update($subscription->id, array(
            'price'   => $subscription->price + 1,
            'options' => array('prorateCharges' => false)
        ));
        $this->assertTrue($result->success);
        $this->assertEquals(sizeof($subscription->transactions), sizeof($result->subscription->transactions));
    }

    function testUpdate_DoesNotUpdateSubscriptionWhenProrationTransactionFailsAndRevertIsTrue()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::update($subscription->id, array(
            'price'   => $subscription->price + 2100,
            'options' => array('prorateCharges' => true, 'revertSubscriptionOnProrationFailure' => true)
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(sizeof($subscription->transactions) + 1, sizeof($result->subscription->transactions));
        $this->assertEquals(Transaction::PROCESSOR_DECLINED, $result->subscription->transactions[0]->status);
        $this->assertEquals("0.00", $result->subscription->balance);
        $this->assertEquals($subscription->price, $result->subscription->price);
    }

    function testUpdate_UpdatesSubscriptionWhenProrationTransactionFailsAndRevertIsFalse()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::update($subscription->id, array(
            'price'   => $subscription->price + 2100,
            'options' => array('prorateCharges' => true, 'revertSubscriptionOnProrationFailure' => false)
        ));
        $this->assertTrue($result->success);
        $this->assertEquals(sizeof($subscription->transactions) + 1, sizeof($result->subscription->transactions));
        $this->assertEquals(Transaction::PROCESSOR_DECLINED, $result->subscription->transactions[0]->status);
        $this->assertEquals($result->subscription->transactions[0]->amount, $result->subscription->balance);
        $this->assertEquals($subscription->price + 2100, $result->subscription->price);
    }

    function testUpdate_invalidSubscriptionId()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        Subscription::update('does-not-exist', array());
    }

    function testUpdate_validationErrors()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::update($subscription->id, array('price' => ''));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('price');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_PRICE_CANNOT_BE_BLANK, $errors[0]->code);
    }

    function testUpdate_cannotUpdateCanceledSubscription()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        Subscription::cancel($subscription->id);
        $result = Subscription::update($subscription->id, array('price' => '1.00'));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('base');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_CANNOT_EDIT_CANCELED_SUBSCRIPTION, $errors[0]->code);
    }

    function testUpdate_canUpdatePaymentMethodToken()
    {
        $oldCreditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $subscription = Subscription::create(array(
            'paymentMethodToken' => $oldCreditCard->token,
            'price'              => '54.99',
            'planId'             => $plan['id']
        ))->subscription;

        $newCreditCard = CreditCard::createNoValidate(array(
            'number'         => '5105105105105100',
            'expirationDate' => '05/2010',
            'customerId'     => $oldCreditCard->customerId
        ));

        $result = Subscription::update($subscription->id, array(
            'paymentMethodToken' => $newCreditCard->token
        ));
        $this->assertTrue($result->success);
        $this->assertEquals($newCreditCard->token, $result->subscription->paymentMethodToken);
    }

    function testUpdate_canUpdatePaymentMethodWithPaymentMethodNonce()
    {
        $oldCreditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $subscription = Subscription::create(array(
            'paymentMethodToken' => $oldCreditCard->token,
            'price'              => '54.99',
            'planId'             => $plan['id']
        ))->subscription;

        $customerId = Customer::create()->customer->id;
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "customerId" => $oldCreditCard->customerId,
            "share"      => true
        ));

        $result = Subscription::update($subscription->id, array(
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);

        $newCreditCard = CreditCard::find($result->subscription->paymentMethodToken);

        $this->assertEquals("1111", $newCreditCard->last4);
        $this->assertNotEquals($oldCreditCard->last4, $newCreditCard->last4);
    }

    function testUpdate_canUpdateAddOnsAndDiscounts()
    {
        $oldCreditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $subscription = Subscription::create(array(
            'paymentMethodToken' => $oldCreditCard->token,
            'price'              => '54.99',
            'planId'             => $plan['id']
        ))->subscription;

        $result = Subscription::update($subscription->id, array(
            'addOns'    => array(
                'update' => array(
                    array(
                        'amount'                => '99.99',
                        'existingId'            => 'increase_10',
                        'quantity'              => 99,
                        'numberOfBillingCycles' => 99
                    ),
                    array(
                        'amount'       => '22.22',
                        'existingId'   => 'increase_20',
                        'quantity'     => 22,
                        'neverExpires' => true
                    )
                ),
            ),
            'discounts' => array(
                'update' => array(
                    array(
                        'amount'                => '33.33',
                        'existingId'            => 'discount_11',
                        'quantity'              => 33,
                        'numberOfBillingCycles' => 33
                    )
                ),
            ),
        ));
        $this->assertTrue($result->success);

        $subscription = $result->subscription;
        $this->assertEquals(2, sizeof($subscription->addOns));
        $addOns = $subscription->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->id, "increase_10");
        $this->assertEquals($addOns[0]->amount, "99.99");
        $this->assertEquals($addOns[0]->neverExpires, false);
        $this->assertEquals($addOns[0]->numberOfBillingCycles, 99);
        $this->assertEquals($addOns[0]->quantity, 99);

        $this->assertEquals($addOns[1]->id, "increase_20");
        $this->assertEquals($addOns[1]->amount, "22.22");
        $this->assertEquals($addOns[1]->neverExpires, true);
        $this->assertEquals($addOns[1]->numberOfBillingCycles, null);
        $this->assertEquals($addOns[1]->quantity, 22);

        $this->assertEquals(2, sizeof($subscription->discounts));
        $discounts = $subscription->discounts;
        SubscriptionTestHelper::sortModificationsById($discounts);

        $this->assertEquals($discounts[0]->id, "discount_11");
        $this->assertEquals($discounts[0]->amount, "33.33");
        $this->assertEquals($discounts[0]->neverExpires, false);
        $this->assertEquals($discounts[0]->numberOfBillingCycles, 33);
        $this->assertEquals($discounts[0]->quantity, 33);
    }

    function testUpdate_canAddAndRemoveAddOnsAndDiscounts()
    {
        $oldCreditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $subscription = Subscription::create(array(
            'paymentMethodToken' => $oldCreditCard->token,
            'price'              => '54.99',
            'planId'             => $plan['id']
        ))->subscription;

        $result = Subscription::update($subscription->id, array(
            'addOns'    => array(
                'add'    => array(
                    array(
                        'amount'                => '33.33',
                        'inheritedFromId'       => 'increase_30',
                        'quantity'              => 33,
                        'numberOfBillingCycles' => 33
                    )
                ),
                'remove' => array('increase_10', 'increase_20')
            ),
            'discounts' => array(
                'add'    => array(
                    array(
                        'inheritedFromId' => 'discount_15',
                    )
                ),
                'remove' => array('discount_7')
            ),
        ));
        $this->assertTrue($result->success);

        $subscription = $result->subscription;
        $this->assertEquals(1, sizeof($subscription->addOns));
        $addOns = $subscription->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->id, "increase_30");
        $this->assertEquals($addOns[0]->amount, "33.33");
        $this->assertEquals($addOns[0]->neverExpires, false);
        $this->assertEquals($addOns[0]->numberOfBillingCycles, 33);
        $this->assertEquals($addOns[0]->quantity, 33);

        $this->assertEquals(2, sizeof($subscription->discounts));
        $discounts = $subscription->discounts;
        SubscriptionTestHelper::sortModificationsById($discounts);

        $this->assertEquals($discounts[0]->id, "discount_11");
        $this->assertEquals($discounts[1]->id, "discount_15");
        $this->assertEquals($discounts[1]->amount, "15.00");
        $this->assertEquals($discounts[1]->neverExpires, true);
        $this->assertNull($discounts[1]->numberOfBillingCycles);
        $this->assertEquals($discounts[1]->quantity, 1);
    }

    function testUpdate_canReplaceEntireSetOfAddonsAndDiscounts()
    {
        $oldCreditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::addOnDiscountPlan();
        $subscription = Subscription::create(array(
            'paymentMethodToken' => $oldCreditCard->token,
            'price'              => '54.99',
            'planId'             => $plan['id']
        ))->subscription;

        $result = Subscription::update($subscription->id, array(
            'addOns'    => array(
                'add' => array(
                    array('inheritedFromId' => 'increase_30'),
                    array('inheritedFromId' => 'increase_20')
                )
            ),
            'discounts' => array(
                'add' => array(
                    array('inheritedFromId' => 'discount_15')
                )
            ),
            'options'   => array('replaceAllAddOnsAndDiscounts' => true)
        ));
        $this->assertTrue($result->success);
        $subscription = $result->subscription;

        $this->assertEquals(2, sizeof($subscription->addOns));
        $addOns = $subscription->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->id, "increase_20");
        $this->assertEquals($addOns[1]->id, "increase_30");

        $this->assertEquals(1, sizeof($subscription->discounts));
        $discounts = $subscription->discounts;

        $this->assertEquals($discounts[0]->id, "discount_15");
    }

    function testUpdate_withDescriptor()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $subscription = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'descriptor'         => array(
                'name'  => '123*123456789012345678',
                'phone' => '3334445555'
            )
        ))->subscription;
        $result = Subscription::update($subscription->id, array(
            'descriptor' => array(
                'name'  => '999*9999999',
                'phone' => '8887776666'
            )
        ));
        $updatedSubscription = $result->subscription;
        $this->assertEquals('999*9999999', $updatedSubscription->descriptor->name);
        $this->assertEquals('8887776666', $updatedSubscription->descriptor->phone);
    }

    function testCancel_returnsSuccessIfCanceled()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $result = Subscription::cancel($subscription->id);
        $this->assertTrue($result->success);
        $this->assertEquals(Subscription::CANCELED, $result->subscription->status);
    }

    function testCancel_throwsErrorIfRecordNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound');
        Subscription::cancel('non-existing-id');
    }

    function testCancel_returnsErrorIfCancelingCanceledSubscription()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        Subscription::cancel($subscription->id);
        $result = Subscription::cancel($subscription->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('subscription')->onAttribute('status');
        $this->assertEquals(Error_Codes::SUBSCRIPTION_STATUS_IS_CANCELED, $errors[0]->code);
    }

    function testRetryCharge_WithoutAmount()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . '/subscriptions/' . $subscription->id . '/make_past_due';
        $http->put($path);

        $result = Subscription::retryCharge($subscription->id);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertEquals($subscription->price, $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
    }

    function testRetryCharge_WithAmount()
    {
        $subscription = SubscriptionTestHelper::createSubscription();
        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . '/subscriptions/' . $subscription->id . '/make_past_due';
        $http->put($path);

        $result = Subscription::retryCharge($subscription->id, 1000);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(1000, $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
    }
}
