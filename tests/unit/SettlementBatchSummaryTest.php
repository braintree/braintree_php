<?php

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SettlementBatchSummaryTest extends PHPUnit_Framework_TestCase
{
    /**
     * 
     * @var \Braintree_SettlementBatchSummary
     */
    private $summary;
    
    protected function setUp()
    {
        parent::setUp();
        $this->summary = \Braintree_SettlementBatchSummary::factory(array());
    }

    public function testFactory()
    {
        $this->assertInstanceOf('Braintree_SettlementBatchSummary', $this->summary);
    }

}
