<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PayPalPaymentResourceTest extends Setup
{
    public function testUpdateIsSuccessful()
    {
        $nonce = Braintree\Test\Nonces::$paypalOneTimePayment;

        $result = Braintree\PayPalPaymentResource::update([
            'amount' => '55.00',
            'amountBreakdown' => [
                'discount' => '15.00',
                'handling' => '0.00',
                'insurance' => '5.00',
                'itemTotal' => '45.00',
                'shipping' => '10.00',
                'shippingDiscount' => '0.00',
                'taxTotal' => '10.00',
            ],
            'currencyIsoCode' => 'USD',
            'customField' => '0437',
            'description' => 'This is a test',
            'lineItems' => [
                [
                    'description' => 'Shoes',
                    'imageUrl' => 'https://example.com/products/23434/pic.png',
                    'kind' => Braintree\TransactionLineItem::DEBIT,
                    'name' => 'Name #1',
                    'productCode' => '23434',
                    'quantity' => '1',
                    'totalAmount' => '45.00',
                    'unitAmount' => '45.00',
                    'unitTaxAmount' => '10.00',
                    'url' => 'https://example.com/products/23434',
                ],
            ],
            'orderId' => 'order-123456789',
            'payeeEmail' => 'bt_buyer_us@paypal.com',
            'paymentMethodNonce' => $nonce,
            'shipping' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'streetAddress' => '123 Division Street',
                'extendedAddress' => 'Apt. #1',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60618',
                'countryName' => 'United States',
                'countryCodeAlpha2' => 'US',
                'countryCodeAlpha3' => 'USA',
                'countryCodeNumeric' => '484',
                'internationalPhone' => [
                    'countryCode' => '1',
                    'nationalNumber' => '4081111111',
                ],
            ],
            'shippingOptions' => [
            [
                'amount' => '10.00',
                'id' => 'option1',
                'label' => 'fast',
                'selected' => true,
                'type' => 'SHIPPING',
            ],
            ],
        ]);

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethodNonce);
        $this->assertNotNull($result->paymentMethodNonce->nonce);
    }

    public function testUpdateReturnsVaildResponse()
    {
        $nonce = Braintree\Test\Nonces::$paypalOneTimePayment;

        $result = Braintree\PayPalPaymentResource::update([
            'amount' => '55.00',
            'currencyIsoCode' => 'USD',
            'customField' => '0437',
            'description' => 'This is a test',
            'orderId' => 'order-123456789',
            'payeeEmail' => 'bt_buyer_us@paypal',
            'paymentMethodNonce' => $nonce,
            'shippingOptions' => [
            [
                'amount' => '10.00',
                'id' => 'option1',
                'label' => 'fast',
                'selected' => true,
                'type' => 'SHIPPING',
            ],
            ],
        ]);


        $this->assertFalse($result->success);
        $this->assertEquals(
            Braintree\Error\Codes::PAYPAL_PAYMENT_RESOURCE_INVALID_EMAIL,
            $result->errors->deepAll()[0]->code
        );
    }
}
