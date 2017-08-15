<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

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

        $this->assertEquals(0, $collection->maximumCount());
    }

    public function testAdvancedSearch_byId_returnsSingleDispute()
    {
        // $collection = Braintree\Dispute::search([
        //     Braintree\DisputeSearch::id()->is('open_dispute')
        // ]);
        //
        // $this->assertEquals(1, $collection->maximumCount());
    }

    public function testAdvancededSearch_byReason_returnsTwoDisputes()
    {
        // $collection = Braintree\Dispute::search([
        //     Braintree\DisputeSearch::reason->in([
        //         Braintree\Dispute::PRODUCT_UNSATISFACTORY,
        //         Braintree\Dispute::RETRIEVAL
        //     ])
        // ]);
        //
        // self->assertEquals(2, $collection->maximumCount());
    }

    public function testAdvancedSearch_byDateRange_returnsDispute()
    {
        // $collection = Braintree\Dispute::search([
        //     Braintree\DisputeSearch::receivedDate->between(
        //         "03/03/2014",
        //         "03/05/2014"
        //     )
        // ]);
        //
        // self->assertEquals(1, $collection->maximumCount());
        // self->assertEquals($collection[0]->receivedDate, DateTime::createFromFormat('Ymd', '20140304'));
    }
}
