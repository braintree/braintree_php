<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_PlanTest extends PHPUnit_Framework_TestCase
{
    function testAll_returnsAllPlans()
    {
        $newId = strval(rand());
        $params = array (
            "id" => $newId,
            "billingDayOfMonth" => "1",
            "billingFrequency" => "1",
            "currencyIsoCode" => "USD",
            "description" => "some description",
            "name" => "php test plan",
            "numberOfBillingCycles" => "1",
            "price" => "1.00",
            "trialDuration" => "3",
            "trialDurationUnit" => "day",
            "trialPeriod" => "true"
        );

        Braintree_Http::post("/plans/create_plan_for_tests", array("plan" => $params));

        $addOnParams = array (
            "kind" => "add_on",
            "plan_id" => $newId,
            "amount" => "1.00",
            "name" => "add_on_name"
        );

        Braintree_Http::post("/modifications/create_modification_for_tests", array("modification" => $addOnParams));

        $discountParams = array (
            "kind" => "discount",
            "plan_id" => $newId,
            "amount" => "1.00",
            "name" => "discount_name"
        );

        Braintree_Http::post("/modifications/create_modification_for_tests", array("modification" => $discountParams));

        $plans = Braintree_Plan::all();

        foreach ($plans as $plan)
        {
            if ($plan->id == $newId)
            {
                $actualPlan = $plan;
            }
        }

        $this->assertNotNull($actualPlan);
        $this->assertEquals($params["billingDayOfMonth"], $actualPlan->billingDayOfMonth);
        $this->assertEquals($params["billingFrequency"], $actualPlan->billingFrequency);
        $this->assertEquals($params["currencyIsoCode"], $actualPlan->currencyIsoCode);
        $this->assertEquals($params["description"], $actualPlan->description);
        $this->assertEquals($params["name"], $actualPlan->name);
        $this->assertEquals($params["numberOfBillingCycles"], $actualPlan->numberOfBillingCycles);
        $this->assertEquals($params["price"], $actualPlan->price);
        $this->assertEquals($params["trialDuration"], $actualPlan->trialDuration);
        $this->assertEquals($params["trialDurationUnit"], $actualPlan->trialDurationUnit);
        $this->assertEquals($params["trialPeriod"], $actualPlan->trialPeriod);

        $addOn = $actualPlan->addOns[0];
        $this->assertEquals($addOnParams["name"], $addOn->name);

        $discount = $actualPlan->discounts[0];
        $this->assertEquals($discountParams["name"], $discount->name);
    }
}
