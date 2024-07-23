<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class PackageTrackingTest extends Setup
{
    public function testPackageTrackingHandlesInvalidRequest()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$paypalOneTimePayment;

        // Create Transaction
        $result = Braintree\Transaction::sale([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'options' => [
                'submitForSettlement' => true,
            ],
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        // carrier name missing
        $invalidResult = Braintree\Transaction::packageTracking($transaction->id, [ 'trackingNumber' => "tracking_number_1", ]);
        $this->assertFalse($invalidResult->success);
        $this->assertEquals('Carrier name is required.', $invalidResult->message);

        // Tracking number is required
        $invalidResult = Braintree\Transaction::packageTracking($transaction->id, [ 'carrier' => "UPS", ]);
        $this->assertFalse($invalidResult->success);
        $this->assertEquals('Tracking number is required.', $invalidResult->message);
    }

    public function testPackageTracking()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Braintree\Test\Nonces::$paypalOneTimePayment;

        // Create Transaction
        $result = Braintree\Transaction::sale([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'options' => [
                'submitForSettlement' => true,
            ],
            'paymentMethodNonce' => $nonce
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        // Create First package with 2 products
        $firstPackageResult = Braintree\Transaction::packageTracking(
            $transaction->id,
            [
                'carrier' => "UPS",
                'trackingNumber' => "tracking_number_1",
                'notifyPayer' => true,
                'lineItems' => [
                    [
                        'quantity' => '1',
                        'name' => 'Best Product Ever',
                        'productCode' => "ABC 01",
                        'description' => "Best Description Ever",
                        'upcCode' => "51234567890",
                        'upcType' => "UPC-A",
                        'imageUrl' => "https://example.com/image.png",
                    ],
                    [
                        'quantity' => '1',
                        'name' => 'Best Product Ever',
                        'productCode' => "ABC 02",
                        'description' => "Best Description Ever",
                        'upcCode' => "51234567891",
                        'upcType' => "UPC-A",
                        'imageUrl' => "https://example.com/image.png",
                    ]
                ]

            ]
        );

        // First package is shipped by the merchant
        $this->assertTrue($firstPackageResult->success);
        $this->assertNotNull($firstPackageResult->transaction->packages[0]->id);
        $this->assertEquals('UPS', $firstPackageResult->transaction->packages[0]->carrier);
        $this->assertEquals('tracking_number_1', $firstPackageResult->transaction->packages[0]->trackingNumber);

        // Create second package with 1 product
        $secondPackageResult = Braintree\Transaction::packageTracking(
            $transaction->id,
            [
                'carrier' => "FEDEX",
                'trackingNumber' => "tracking_number_2",
                'notifyPayer' => true,
                'lineItems' => [
                    [
                        'quantity' => '1',
                        'name' => 'Best Product Ever',
                        'productCode' => "ABC 03",
                        'description' => "Best Description Ever"
                    ]
                ]
            ]
        );

        // Second package is shipped by the merchant
        $this->assertTrue($secondPackageResult->success);
        $this->assertNotNull($secondPackageResult->transaction->packages[1]->id);
        $this->assertEquals('FEDEX', $secondPackageResult->transaction->packages[1]->carrier);
        $this->assertEquals('tracking_number_2', $secondPackageResult->transaction->packages[1]->trackingNumber);

        // Find transaction gives both packages
        $findTransaction = Braintree\Transaction::find($transaction->id);
        $this->assertEquals(2, count($findTransaction->packages));
    }

    public function testPackageTrackingRetrievingTrackers()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);

        // Find transaction gives both packages
        $findTransaction = Braintree\Transaction::find('package_tracking_tx');

        $packages = $findTransaction->packages;

        $this->assertEquals(2, count($packages));
        $this->assertEquals('paypal_tracker_id_1', $packages[0]->paypalTrackerId);
        $this->assertEquals('paypal_tracker_id_2', $packages[1]->paypalTrackerId);
    }
}
