<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test;
use Test\Setup;
use Braintree;

class DisputeSearchTest extends Setup
{
    public function testAdvancedSearch_noResults()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::id()->is('non_existent_dispute')
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(0, count($disputes));
    }

    public function testAdvancedSearch_byId_returnsSingleDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::id()->is('open_dispute')
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(1, count($disputes));
    }

    public function testAdvancedSearch_byCustomerid_returnsDispute()
    {
        $customerId = Braintree\Customer::create()->customer->id;
        Braintree\Transaction::sale([
            'amount' => '10.00',
            'customerId' => $customerId,
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$disputes['Chargeback'],
                'expirationDate' => "12/20"
            ]
        ]);

        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::customerId()->is($customerId)
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(1, count($disputes));
    }

    public function testAdvancededSearch_byReason_returnsTwoDisputes()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::reason()->in([
                Braintree\Dispute::PRODUCT_UNSATISFACTORY,
                Braintree\Dispute::RETRIEVAL
            ])
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertGreaterThanOrEqual(2, count($disputes));
    }

    public function testAdvancededSearch_byChargebackProtectionLevel_returnsDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::chargebackProtectionLevel()->in([
                Braintree\Dispute::EFFORTLESS
            ])
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(1, count($disputes));
        $this->assertEquals($disputes[0]->caseNumber, "CASE-CHARGEBACK-PROTECTED");
        $this->assertEquals($disputes[0]->reason, Braintree\Dispute::FRAUD);
        // NEXT_MAJOR_VERSION Remove this assertion when chargebackProtectionLevel is removed from the SDK
        $this->assertEquals($disputes[0]->chargebackProtectionLevel, Braintree\Dispute::EFFORTLESS);
        $this->assertEquals($disputes[0]->protectionLevel, Braintree\Dispute::EFFORTLESS_CBP);
    }

    public function testAdvancedSearch_byPreDisputeProgram_returnsDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::preDisputeProgram()->in([
                Braintree\Dispute::VISA_RDR
            ])
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(1, count($disputes));
        $this->assertEquals($disputes[0]->preDisputeProgram, Braintree\Dispute::VISA_RDR);
    }

    public function testAdvancedSearch_forNonPreDisputes_returnsDisputes()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::preDisputeProgram()->is(Braintree\Dispute::NONE)
        ]);

        $disputes = $this->collectionToArray($collection);
        $preDisputePrograms = array_unique(array_map(function ($d) {
            return $d->preDisputeProgram;
        }, $disputes));

        $this->assertGreaterThan(1, count($disputes));
        $this->assertEquals(1, count($preDisputePrograms));
        $this->assertTrue(in_array(Braintree\Dispute::NONE, $preDisputePrograms));
    }

    public function testAdvancedSearch_byReceivedDateRange_returnsDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::receivedDate()->between(
                "03/03/2014",
                "03/05/2014"
            )
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertGreaterThanOrEqual(1, count($disputes));
        $this->assertEquals($disputes[0]->receivedDate, DateTime::createFromFormat('Ymd His', '20140304 000000'));
    }

    public function testAdvancedSearch_byDisbursementDateRange_returnsDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::disbursementDate()->between(
                "03/03/2014",
                "03/05/2014"
            )
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertGreaterThanOrEqual(1, count($disputes));
        $this->assertEquals($disputes[0]->statusHistory[0]->effectiveDate, DateTime::createFromFormat('Ymd His', '20140304 000000'));
    }

    public function testAdvancedSearch_byEffectiveDateRange_returnsDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::disbursementDate()->between(
                "03/03/2014",
                "03/05/2014"
            )
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertGreaterThanOrEqual(1, count($disputes));
        $this->assertEquals($disputes[0]->statusHistory[0]->disbursementDate, DateTime::createFromFormat('Ymd His', '20140305 000000'));
    }

    private function collectionToArray($collection)
    {
        $array = [];
        foreach ($collection as $element) {
            array_push($array, $element);
        }
        return $array;
    }
}
