<?php namespace Braintree\Tests\Integration;

use Braintree\Configuration;
use Braintree\Gateway;
use Braintree\Http;
use Braintree\Plan;
use Braintree\Tests\TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class PlanTest extends \PHPUnit_Framework_TestCase
{
    function testAll_withNoPlans_returnsEmptyArray()
    {
        TestHelper::testMerchantConfig();
        $plans = Plan::all();
        $this->assertEquals($plans, array());
        TestHelper::integrationMerchantConfig();

    }

    function testAll_returnsAllPlans()
    {
        $newId = strval(rand());
        $params = array(
            "id"                    => $newId,
            "billingDayOfMonth"     => "1",
            "billingFrequency"      => "1",
            "currencyIsoCode"       => "USD",
            "description"           => "some description",
            "name"                  => "php test plan",
            "numberOfBillingCycles" => "1",
            "price"                 => "1.00",
            "trialPeriod"           => "false"
        );

        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . "/plans/create_plan_for_tests";
        $http->post($path, array("plan" => $params));

        $addOnParams = array(
            "kind"    => "add_on",
            "plan_id" => $newId,
            "amount"  => "1.00",
            "name"    => "add_on_name"
        );

        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . "/modifications/create_modification_for_tests";
        $http->post($path, array("modification" => $addOnParams));

        $discountParams = array(
            "kind"    => "discount",
            "plan_id" => $newId,
            "amount"  => "1.00",
            "name"    => "discount_name"
        );

        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . "/modifications/create_modification_for_tests";
        $http->post($path, array("modification" => $discountParams));

        $plans = Plan::all();

        foreach ($plans as $plan) {
            if ($plan->id == $newId) {
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

        $addOn = $actualPlan->addOns[0];
        $this->assertEquals($addOnParams["name"], $addOn->name);

        $discount = $actualPlan->discounts[0];
        $this->assertEquals($discountParams["name"], $discount->name);
    }

    function testGatewayAll_returnsAllPlans()
    {
        $newId = strval(rand());
        $params = array(
            "id"                    => $newId,
            "billingDayOfMonth"     => "1",
            "billingFrequency"      => "1",
            "currencyIsoCode"       => "USD",
            "description"           => "some description",
            "name"                  => "php test plan",
            "numberOfBillingCycles" => "1",
            "price"                 => "1.00",
            "trialPeriod"           => "false"
        );

        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . "/plans/create_plan_for_tests";
        $http->post($path, array("plan" => $params));

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $plans = $gateway->plan()->all();

        foreach ($plans as $plan) {
            if ($plan->id == $newId) {
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
    }
}
