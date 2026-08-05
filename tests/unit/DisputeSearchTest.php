<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class DisputeSearchTest extends Setup
{
    public function testSearch_amountDisputed_isRangeNode()
    {
        $node = Braintree\DisputeSearch::amountDisputed();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_amountWon_isRangeNode()
    {
        $node = Braintree\DisputeSearch::amountWon();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_caseNumber_isTextNode()
    {
        $node = Braintree\DisputeSearch::caseNumber();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_id_isTextNode()
    {
        $node = Braintree\DisputeSearch::id();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerId_isTextNode()
    {
        $node = Braintree\DisputeSearch::customerId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_kind_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::kind();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_merchantAccountId_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::merchantAccountId();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_reason_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::reason();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_reasonCode_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::reasonCode();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_receivedDate_isRangeNode()
    {
        $node = Braintree\DisputeSearch::receivedDate();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_disbursementDate_isRangeNode()
    {
        $node = Braintree\DisputeSearch::disbursementDate();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_effectiveDate_isRangeNode()
    {
        $node = Braintree\DisputeSearch::effectiveDate();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_referenceNumber_isTextNode()
    {
        $node = Braintree\DisputeSearch::referenceNumber();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_replyByDate_isRangeNode()
    {
        $node = Braintree\DisputeSearch::replyByDate();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_status_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::status();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    // NEXT_MAJOR_VERSION chargebackProtectionLevel is deprecated in favor of protectionLevel
    public function testSearch_chargebackProtectionLevel_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::chargebackProtectionLevel();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_protectionLevel_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::protectionLevel();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_preDisputeProgram_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::preDisputeProgram();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_transactionId_isTextNode()
    {
        $node = Braintree\DisputeSearch::transactionId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_transactionSource_isMultipleValueNode()
    {
        $node = Braintree\DisputeSearch::transactionSource();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_protectionLevel_allowsValidValues()
    {
        $node = Braintree\DisputeSearch::protectionLevel();
        $values = Braintree\Dispute::allProtectionLevelTypes();
        $this->assertEquals($values, $node->in($values)->toParam());
    }
}
