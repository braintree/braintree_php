<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PackageTrackingTest extends Setup
{
    public function testTransactionWithoutPackages()
    {
        $transaction = Braintree\Transaction::factory([
            'id' => '123',
            'shipments' => []
        ]);

        $packages = $transaction -> packages;
        $this->assertEquals(0, count($packages));
    }

    public function testTransactionWithoutPackagesTag()
    {
        $transaction = Braintree\Transaction::factory([
            'id' => '123',
        ]);

        $packages = $transaction -> packages;
        $this->assertEquals(0, count($packages));
    }

    public function testTransactionWithPackages()
    {
        $transaction = Braintree\Transaction::factory([
            'id' => '123',
            'shipments' => [
                [
                    'id' => 'id1',
                    'carrier' => "UPS",
                    'trackingNumber' => "tracking_number_1",
                    // NEXT_MAJOR_VERSION Remove paypalTrackingId
                    'paypalTrackingId' => 'pp_tracking_number_1',
                    'paypalTrackerId' => 'pp_tracker_id_1',
                ],
                [
                    'id' => 'id2',
                    'carrier' => "FEDEX",
                    'trackingNumber' => "tracking_number_2",
                    // NEXT_MAJOR_VERSION Remove paypalTrackingId
                    'paypalTrackingId' => 'pp_tracking_number_2',
                    'paypalTrackerId' => 'pp_tracker_id_2',
                ]
            ]
        ]);

        $packages = $transaction -> packages;
        $this->assertEquals("id1", $packages[0]->id);
        $this->assertEquals("UPS", $packages[0]->carrier);
        $this->assertEquals("tracking_number_1", $packages[0]->trackingNumber);
        // NEXT_MAJOR_VERSION Remove paypalTrackingId assertion
        $this->assertEquals("pp_tracking_number_1", $packages[0]->paypalTrackingId);
        $this->assertEquals("pp_tracker_id_1", $packages[0]->paypalTrackerId);

        $this->assertEquals("id2", $packages[1]->id);
        $this->assertEquals("FEDEX", $packages[1]->carrier);
        $this->assertEquals("tracking_number_2", $packages[1]->trackingNumber);
        // NEXT_MAJOR_VERSION Remove paypalTrackingId assertion
        $this->assertEquals("pp_tracking_number_2", $packages[1]->paypalTrackingId);
        $this->assertEquals("pp_tracker_id_2", $packages[1]->paypalTrackerId);
    }

    public function testPackageTrackingRequest()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: invalid_key');
        Braintree\Transaction::packageTracking("txn123", ['invalid_key' => "random"]);
    }
}
