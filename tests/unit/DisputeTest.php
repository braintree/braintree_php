<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class DisputeTest extends Setup
{
    private $attributes;

    public function __construct() {
        $this->attributes = [
            'amount' => '100.00',
            'amount_disputed' => '100.00',
            'amount_won' => '0.00',
            'case_number' => 'CB123456',
            'created_at' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            'currency_iso_code' => 'USD',
            'date_opened' => DateTime::createFromFormat('Ymd-His', '20130401-000000'),
            'date_won' => DateTime::createFromFormat('Ymd-His', '20130402-000000'),
            'forwarded_comments' => 'Forwarded comments',
            'id' => '123456',
            'kind' => 'chargeback',
            'merchant_account_id' => 'abc123',
            'original_dispute_id' => 'original_dispute_id',
            'reason' => 'fraud',
            'reason_code' => '83',
            'reason_description' => 'Reason code 83 description',
            'received_date' => DateTime::createFromFormat('Ymd', '20130410'),
            'reference_number' => '123456',
            'reply_by_date' => DateTime::createFromFormat('Ymd', '20130417'),
            'status' => 'open',
            'updated_at' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            'evidence' => [[
                'comment' => NULL,
                'created_at' => DateTime::createFromFormat('Ymd-His', '20130411-105039'),
                'id' => 'evidence1',
                'sent_to_processor_at' => NULL,
                'url' => 'url_of_file_evidence',
            ],[
                'comment' => 'text evidence',
                'created_at' => DateTime::createFromFormat('Ymd-His', '20130411-105039'),
                'id' => 'evidence2',
                'sent_to_processor_at' => '2009-04-11',
                'url' => NULL,
            ]],
            'status_history' => [[
                'effective_date' => '2013-04-10',
                'status' => 'open',
                'timestamp' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            ]],
            'transaction' => [
                'id' => 'transaction_id',
                'amount' => '100.00',
                'created_at' => DateTime::createFromFormat('Ymd-His', '20130319-105039'),
                'order_id' => NULL,
                'purchase_order_number' => 'po',
                'payment_instrument_subtype' => 'Visa',
            ]
        ];
    }

    public function test_legacy_constructor()
    {
        $legacyParams = [
            'transaction' => [
                'id' => 'transaction_id',
                'amount' => '100.00',
            ],
            'id' => '123456',
            'currency_iso_code' => 'USD',
            'status' => 'open',
            'amount' => '100.00',
            'received_date' => DateTime::createFromFormat('Ymd', '20130410'),
            'reply_by_date' => DateTime::createFromFormat('Ymd', '20130410'),
            'reason' => 'fraud',
            'transaction_ids' => [
                'asdf', 'qwer'
            ],
            'date_opened' => DateTime::createFromFormat('Ymd', '20130401'),
            'date_won' =>DateTime::createFromFormat('Ymd', '20130402'),
            'kind' => 'chargeback'
        ];

        $dispute = Braintree\Dispute::factory($legacyParams);

        $this->assertEquals('123456', $dispute->id);
        $this->assertEquals('100.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currency_iso_code);
        $this->assertEquals(Braintree\Dispute::FRAUD, $dispute->reason);
        $this->assertEquals(Braintree\Dispute::OPEN, $dispute->status);
        $this->assertEquals(Braintree\Dispute::Open, $dispute->status);
        $this->assertEquals('transaction_id', $dispute->transactionDetails->id);
        $this->assertEquals('100.00', $dispute->transactionDetails->amount);
        $this->assertEquals(DateTime::createFromFormat('Ymd', '20130401'), $dispute->date_opened);
        $this->assertEquals(DateTime::createFromFormat('Ymd', '20130402'), $dispute->date_won);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $dispute->kind);
    }

    public function test_legacy_params_with_new_attributes()
    {
        $dispute = Braintree\Dispute::factory($this->attributes);

        $this->assertEquals('123456', $dispute->id);
        $this->assertEquals('100.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currency_iso_code);
        $this->assertEquals(Braintree\Dispute::FRAUD, $dispute->reason);
        $this->assertEquals(Braintree\Dispute::Open, $dispute->status);
        $this->assertEquals(Braintree\Dispute::OPEN, $dispute->status);
        $this->assertEquals('transaction_id', $dispute->transactionDetails->id);
        $this->assertEquals('100.00', $dispute->transactionDetails->amount);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130401-000000'), $dispute->date_opened);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130402-000000'), $dispute->date_won);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $dispute->kind);
    }

    public function test_constructor_populates_new_fields()
    {
        $dispute = Braintree\Dispute::factory($this->attributes);

        $this->assertEquals("100.00", $dispute->amount_disputed);
        $this->assertEquals("0.00", $dispute->amount_won);
        $this->assertEquals("CB123456", $dispute->case_number);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->created_at);
        $this->assertEquals("Forwarded comments", $dispute->forwarded_comments);
        $this->assertEquals("abc123", $dispute->merchant_account_id);
        $this->assertEquals("original_dispute_id", $dispute->original_dispute_id);
        $this->assertEquals("83", $dispute->reason_code);
        $this->assertEquals("Reason code 83 description", $dispute->reason_description);
        $this->assertEquals("123456", $dispute->reference_number);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->updated_at);
        $this->assertNull($dispute->evidence[0]->comment);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130411-105039'), $dispute->evidence[0]->created_at);
        $this->assertEquals('evidence1', $dispute->evidence[0]->id);
        $this->assertNull($dispute->evidence[0]->sent_to_processor_at);
        $this->assertEquals('url_of_file_evidence', $dispute->evidence[0]->url);
        $this->assertEquals('text evidence', $dispute->evidence[1]->comment);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130411-105039'), $dispute->evidence[1]->created_at);
        $this->assertEquals('evidence2', $dispute->evidence[1]->id);
        $this->assertEquals('2009-04-11', $dispute->evidence[1]->sent_to_processor_at);
        $this->assertNull($dispute->evidence[1]->url);
        $this->assertEquals('2013-04-10', $dispute->status_history[0]->effective_date);
        $this->assertEquals('open', $dispute->status_history[0]->status);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->status_history[0]->timestamp);
    }

    public function test_constructor_handles_null_fields()
    {
        $emptyAttributes = [
            'amount' => NULL,
            'date_opened' => NULL,
            'date_won' => NULL,
            'evidence' => NULL,
            'reply_by_date' => NULL,
            'status_history' => NULL
        ];

        $attrs = array_merge([], $this->attributes, $emptyAttributes);

        $dispute = Braintree\Dispute::factory($attrs);

        $this->assertNull($dispute->amount);
        $this->assertNull($dispute->date_opened);
        $this->assertNull($dispute->date_won);
        $this->assertNull($dispute->evidence);
        $this->assertNull($dispute->reply_by_date);
        $this->assertNull($dispute->status_history);
    }

    public function test_constructor_populates_transaction()
    {
        $dispute = Braintree\Dispute::factory($this->attributes);

        $this->assertEquals('transaction_id', $dispute->transaction->id);
        $this->assertEquals('100.00', $dispute->transaction->amount);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130319-105039'), $dispute->transaction->created_at);
        $this->assertNull($dispute->transaction->order_id);
        $this->assertEquals('po', $dispute->transaction->purchase_order_number);
        $this->assertEquals('Visa', $dispute->transaction->payment_instrument_subtype);
    }

    public function test_accept_null_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->accept(null);
    }

	public function test_accept_empty_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->accept(" ");
    }

	public function test_add_text_evidence_empty_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addTextEvidence(" ", "evidence");
    }

	public function test_add_text_evidence_null_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addTextEvidence(null, "evidence");
    }

	public function test_add_text_evidence_empty_evidence_raises_value_exception()
    {
        $this->setExpectedException('InvalidArgumentException', 'content cannot be blank');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addTextEvidence("dispute_id", " ");
    }

	public function test_add_text_evidence_null_evidence_raises_value_exception()
    {
        $this->setExpectedException('InvalidArgumentException', 'content cannot be blank');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addTextEvidence("dispute_id", null);
    }

	public function test_add_file_evidence_empty_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addFileEvidence(" ", 1);
    }

	public function test_add_file_evidence_null_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addFileEvidence(null, 1);
    }

	public function test_add_file_evidence_empty_evidence_raises_value_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'document with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addFileEvidence("dispute_id", " ");
    }

	public function test_add_file_evidence_null_evidence_raises_value_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'document with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->addFileEvidence("dispute_id", null);
    }

	public function test_finalize_null_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->finalize(null);
    }

	public function test_finalize_empty_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->finalize(" ");
    }

	public function test_finding_null_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->find(null);
    }

	public function test_finding_empty_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->find(" ");
    }

	public function test_remove_evidence_empty_dispute_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'evidence with id "evidence" for dispute with id " " not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->removeEvidence(" ", "evidence");
    }

	public function test_remove_evidence_null_dispute_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'evidence with id "evidence" for dispute with id "" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->removeEvidence(null, "evidence");
    }

	public function test_remove_evidence_evidence_null_id_raises_not_found_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'evidence with id "" for dispute with id "dispute_id" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->removeEvidence("dispute_id", null);
    }

	public function test_remove_evidence_empty_evidence_id_raises_value_exception()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'evidence with id " " for dispute with id "dispute_id" not found');

        $args = Braintree\Configuration::gateway();
        $disputeGateway = new Braintree\DisputeGateway($args);

        $disputeGateway->removeEvidence("dispute_id", " ");
    }
}
