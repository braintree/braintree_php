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

    public function testAdvancededSearch_byReason_returnsTwoDisputes()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::reason()->in([
                Braintree\Dispute::PRODUCT_UNSATISFACTORY,
                Braintree\Dispute::RETRIEVAL
            ])
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(2, count($disputes));
    }

    public function testAdvancedSearch_byDateRange_returnsDispute()
    {
        $collection = Braintree\Dispute::search([
            Braintree\DisputeSearch::receivedDate()->between(
                "03/03/2014",
                "03/05/2014"
            )
        ]);

        $disputes = $this->collectionToArray($collection);

        $this->assertEquals(1, count($disputes));
        $this->assertEquals($disputes[0]->receivedDate, DateTime::createFromFormat('Ymd His', '20140304 000000'));
    }

    private function collectionToArray($collection) {
        $array = [];
        foreach($collection as $element) {
            array_push($array, $element);
        }
        return $array;
    }
}
