<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class DisputeTest extends Setup
{
    private $gateway;

    public function __construct() {
        $this->gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
    }

    public function createSampleDocument()
    {
        $pngFile = fopen(dirname(__DIR__) . '/fixtures/bt_logo.png', 'rb');

        $result = Braintree\DocumentUpload::create([
            "kind" => Braintree\DocumentUpload::EVIDENCE_DOCUMENT,
            "file" => $pngFile
        ]);

        return $result->documentUpload;
    }

    public function createSampleDispute()
    {
        $result = Braintree\Transaction::sale([
            'amount' => '100.00',
            'creditCard' => [
                'number' => Braintree\Test\CreditCardNumbers::$disputes['Chargeback'],
                'expirationDate' => '12/2019',
            ]
        ]);
        return $result->transaction->disputes[0];
    }

    public function testAccept_changesDisputeStatusToAccepted()
    {
        $dispute = $this->createSampleDispute();
        $result = $this->gateway->dispute()->accept($dispute->id);

        $this->assertTrue($result->success);

        $updatedDispute = $this->gateway->dispute()->find($dispute->id);

        $this->assertEquals(Braintree\Dispute::ACCEPTED, $updatedDispute->status);
    }

    public function testAccept_errors_whenDisputeNotOpen()
    {
        $result = $this->gateway->dispute()->accept("wells_dispute");
        $error = $result->errors->forKey('dispute')->errors[0];

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Error\Codes::DISPUTE_CAN_ONLY_ACCEPT_OPEN_DISPUTE, $error->code);
        $this->assertEquals("Disputes can only be accepted when they are in an Open state", $error->message);
    }

    public function testAccept_raisesError_whenDisputeNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "invalid-id" not found');
        $this->gateway->dispute()->accept("invalid-id");
    }

    public function testAddFileEvidence_addsEvidence()
    {
        $disputeId = $this->createSampleDispute()->id;
        $documentId = $this->createSampleDocument()->id;

        $result = $this->gateway->dispute()->addFileEvidence($disputeId, $documentId);

        $this->assertTrue($result->success);

        $updatedDispute = $this->gateway->dispute()->find($disputeId);

        $this->assertEquals($result->evidence->id, $updatedDispute->evidence[0]->id);
    }

    public function testAddFileEvidence_raisesError_whenDisputeNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "unknown_dispute_id" not found');
        $this->gateway->dispute()->addFileEvidence("unknown_dispute_id", "unknown_file_id");
    }

    public function testAddFileEvidence_raisesError_whenDisputeNotOpen()
    {
        $disputeId = $this->createSampleDispute()->id;
        $documentId = $this->createSampleDocument()->id;

        $this->gateway->dispute()->accept($disputeId);

        $result = $this->gateway->dispute()->addFileEvidence($disputeId, $documentId);
        $error = $result->errors->forKey('dispute')->errors[0];

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Error\Codes::DISPUTE_CAN_ONLY_ADD_EVIDENCE_TO_OPEN_DISPUTE, $error->code);
        $this->assertEquals("Evidence can only be attached to disputes that are in an Open state", $error->message);
    }

    public function testAddTextEvidence_addsTextEvidence()
    {
        $disputeId = $this->createSampleDispute()->id;

        $result = $this->gateway->dispute()->addTextEvidence($disputeId, "text evidence");
        $evidence = $result->evidence;

        $this->assertTrue($result->success);
        $this->assertEquals("text evidence", $evidence->comment);
        $this->assertNotNull($evidence->createdAt);
        $this->assertRegExp('/^\w{16,}$/', $evidence->id);
        $this->assertNull($evidence->sentToProcessorAt);
        $this->assertNull($evidence->url);
    }

    public function testAddTextEvidence_raisesError_whenDisputeNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "unknown_dispute_id" not found');
        $dispute = $this->gateway->dispute()->addTextEvidence("unknown_dispute_id", "text evidence");
    }

    public function testAddTextEvidence_raisesError_whenDisputeNotOpen()
    {
        $disputeId = $this->createSampleDispute()->id;

        $this->gateway->dispute()->accept($disputeId);

        $result = $this->gateway->dispute()->addTextEvidence($disputeId, "text evidence");
        $error = $result->errors->forKey('dispute')->errors[0];

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Error\Codes::DISPUTE_CAN_ONLY_ADD_EVIDENCE_TO_OPEN_DISPUTE, $error->code);
        $this->assertEquals("Evidence can only be attached to disputes that are in an Open state", $error->message);
    }

    public function testAddTextEvidence_showsNewRecord_inFind()
    {
        $disputeId = $this->createSampleDispute()->id;

        $evidence = $this->gateway->dispute()->addTextEvidence($disputeId, "text evidence")->evidence;

        $refreshedDispute = $this->gateway->dispute()->find($disputeId);
        $refreshedEvidence = $refreshedDispute->evidence[0];

        $this->assertEquals($evidence->id, $refreshedEvidence->id);
        $this->assertEquals($evidence->comment, $refreshedEvidence->comment);
    }

    public function testFinalize_changesDisputeStatus_toDisputed()
    {
        $disputeId = $this->createSampleDispute()->id;

        $result = $this->gateway->dispute()->finalize($disputeId);

        $this->assertTrue($result->success);

        $updatedDispute = $this->gateway->dispute()->find($disputeId);

        $this->assertEquals(Braintree\Dispute::DISPUTED, $updatedDispute->status);
    }

    public function testFinalize_errors_whenDisputeNotOpen()
    {
        $result = $this->gateway->dispute()->finalize("wells_dispute");
        $error = $result->errors->forKey('dispute')->errors[0];

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Error\Codes::DISPUTE_CAN_ONLY_FINALIZE_OPEN_DISPUTE, $error->code);
        $this->assertEquals("Disputes can only be finalized when they are in an Open state", $error->message);
    }

    public function testFinalize_raisesError_whenDisputeNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "invalid-id" not found');
        $result = $this->gateway->dispute()->finalize("invalid-id");
    }

    public function testFind_returnsDispute_withGivenId()
    {
        $dispute = $this->gateway->dispute()->find("open_dispute");

        $this->assertEquals("31.0", $dispute->amountDisputed);
        $this->assertEquals("0.0", $dispute->amountWon);
        $this->assertEquals("open_dispute", $dispute->id);
        $this->assertEquals(Braintree\Dispute::OPEN, $dispute->status);
        $this->assertEquals("open_disputed_transaction", $dispute->transaction->id);
    }

    public function testFind_raisesError_whenDisputeNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'dispute with id "invalid-id" not found');
        $this->gateway->dispute()->find("invalid-id");
    }

    public function testRemoveEvidence_removesEvidenceFromTheDisupute()
    {
        $disputeId = $this->createSampleDispute()->id;
        $evidenceId = $this->gateway->dispute()->addTextEvidence($disputeId, "text evidence")->evidence->id;

        $result = $this->gateway->dispute()->removeEvidence($disputeId, $evidenceId);

        $this->assertTrue($result->success);
    }

    public function testRemoveEvidence_raisesError_whenDisputeOrEvidenceNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', "evidence with id \"unknown_evidence_id\" for dispute with id \"unknown_dispute_id\" not found");
        $this->gateway->dispute()->removeEvidence("unknown_dispute_id", "unknown_evidence_id");
    }

    public function testRemoveEvidence_errors_whenDisputeNotOpen()
    {
        $disputeId = $this->createSampleDispute()->id;
        $evidenceId = $this->gateway->dispute()->addTextEvidence($disputeId, "text evidence")->evidence->id;

        $this->gateway->dispute()->accept($disputeId);

        $result = $this->gateway->dispute()->removeEvidence($disputeId, $evidenceId);
        $error = $result->errors->forKey('dispute')->errors[0];

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree\Error\Codes::DISPUTE_CAN_ONLY_REMOVE_EVIDENCE_FROM_OPEN_DISPUTE, $error->code);
        $this->assertEquals("Evidence can only be removed from disputes that are in an Open state", $error->message);
    }
}
