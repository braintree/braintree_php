<?php

namespace Test\Unit\GraphQL;

use Braintree\GraphQL\Unions\CustomerRecommendations;
use Braintree\GraphQL\Enums\RecommendedPaymentOption;
use Braintree\GraphQL\Types\PaymentRecommendation;
use PHPUnit\Framework\TestCase;

class CustomerRecommendationsTest extends TestCase
{
    public function testCustomerRecommendationsInitialization()
    {

        $paymentRecommendation = PaymentRecommendation::factory([
              "paymentOption" => RecommendedPaymentOption::PAYPAL,
              "recommendedPriority" => 1
            ]);

        $attributes = [
            'paymentRecommendations' => [
                $paymentRecommendation,
            ]
        ];

        $customerRecommendations = CustomerRecommendations::factory($attributes);

        $paymentOptions = $customerRecommendations->paymentOptions;

        $this->assertEquals(1, count($paymentOptions));

        $this->assertEquals('PAYPAL', $paymentOptions[0]->paymentOption);
        $this->assertEquals(1, $paymentOptions[0]->recommendedPriority);


        $paymentRecommendations = $customerRecommendations->paymentRecommendations;

        $this->assertEquals(1, count($paymentRecommendations));

        $this->assertEquals('PAYPAL', $paymentRecommendations[0]->paymentOption);
        $this->assertEquals(1, $paymentRecommendations[0]->recommendedPriority);
    }
}
