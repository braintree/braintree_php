<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class PlanTest extends Setup
{
    public function testAll_withNoPlans_returnsEmptyArray()
    {
        Helper::testMerchantConfig();
        $plans = Braintree\Plan::all();
        $this->assertEquals($plans, []);
        self::integrationMerchantConfig();
    }

    public function testAll_returnsAllPlans()
    {
        $newId = strval(rand());
        $params = [
            "id" => $newId,
            "billingDayOfMonth" => "1",
            "billingFrequency" => "1",
            "currencyIsoCode" => "USD",
            "description" => "some description",
            "name" => "php test plan",
            "numberOfBillingCycles" => "1",
            "price" => "1.00",
            "trialPeriod" => "false"
        ];

        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/plans/create_plan_for_tests';
        $http->post($path, ["plan" => $params]);

        $addOnParams = [
            "kind" => "add_on",
            "plan_id" => $newId,
            "amount" => "1.00",
            "name" => "add_on_name"
        ];

        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/modifications/create_modification_for_tests';
        $http->post($path, ['modification' => $addOnParams]);

        $discountParams = [
            "kind" => "discount",
            "plan_id" => $newId,
            "amount" => "1.00",
            "name" => "discount_name"
        ];

        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/modifications/create_modification_for_tests';
        $http->post($path, ["modification" => $discountParams]);

        $plans = Braintree\Plan::all();

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

    public function testGatewayAll_returnsAllPlans()
    {
        $newId = strval(rand());
        $params = [
            "id" => $newId,
            "billingDayOfMonth" => "1",
            "billingFrequency" => "1",
            "currencyIsoCode" => "USD",
            "description" => "some description",
            "name" => "php test plan",
            "numberOfBillingCycles" => "1",
            "price" => "1.00",
            "trialPeriod" => "false"
        ];

        $http = new Braintree\Http(Braintree\Configuration::$global);
        $path = Braintree\Configuration::$global->merchantPath() . '/plans/create_plan_for_tests';
        $http->post($path, ["plan" => $params]);

        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
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

    public function testCreate_doesNotAcceptBadAttributes()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: bad');
        $result = Braintree\Subscription::create([
            'bad' => 'value'
        ]);
    }

    public function testCreate_whenSuccessful()
    {
        $newId = strval(rand());
        $params = [
            "id" => $newId,
            "billingDayOfMonth" => "12",
            "billingFrequency" => "1",
            "currencyIsoCode" => "USD",
            "description" => "my description",
            "name" => "my plan name",
            "numberOfBillingCycles" => "1",
            "price" => "9.99",
            "trialPeriod" => "false"
        ];

        $result = Braintree\Plan::create($params);
        $this->assertTrue($result->success);
        $plan = $result->plan;
        $this->assertEquals(12, $plan->billingDayOfMonth);
        $this->assertEquals("USD", $plan->currencyIsoCode);
        $this->assertEquals("my plan name", $plan->name);
        $this->assertEquals("9.99", $plan->price);
        $this->assertEquals(1, $plan->numberOfBillingCycles);
    }

    public function testFind()
    {
        $params = [
            "billingDayOfMonth" => "12",
            "billingFrequency" => "1",
            "currencyIsoCode" => "USD",
            "description" => "my description",
            "name" => "my plan name",
            "numberOfBillingCycles" => "1",
            "price" => "9.99",
            "trialPeriod" => "false"
        ];

        $result = Braintree\Plan::create($params);
        $this->assertTrue($result->success);
        $plan = Braintree\Plan::find($result->plan->id);
        $this->assertEquals($result->plan->id, $plan->id);
        $this->assertEquals($result->plan->price, $plan->price);
    }

    public function testFind_throwsIfNotFound()
    {
        $this->expectException('Braintree\Exception\NotFound', 'plan with id does-not-exist not found');
        Braintree\Plan::find('does-not-exist');
    }

    public function testUpdate_doesNotAcceptBadAttributes()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: bad');
        $result = Braintree\Plan::update('id', [
            'bad' => 'value'
        ]);
    }

    public function testUpdate_whenSuccessful()
    {
        $createParams = [
            "billingDayOfMonth" => "12",
            "billingFrequency" => "1",
            "currencyIsoCode" => "USD",
            "description" => "my description",
            "name" => "my plan name",
            "numberOfBillingCycles" => "1",
            "price" => "9.99",
            "trialPeriod" => "false"
        ];

        $createResult = Braintree\Plan::create($createParams);
        $this->assertTrue($createResult->success);
        $plan = $createResult->plan;

        $updatedParams = [
            "name" => "my updated plan name",
            "price" => "99.99"
        ];
        $updatedResult = Braintree\Plan::update($plan->id, $updatedParams);
        $this->assertTrue($updatedResult->success);
        $updatedPlan = $updatedResult->plan;
        $this->assertEquals($updatedPlan->price, "99.99");
        $this->assertEquals($updatedPlan->name, "my updated plan name");
    }
}
