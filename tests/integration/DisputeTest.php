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

    public function testCreate_evidenceDocument()
    {
    }

    public function createSampleDispute()
    {
        $result = Braintree\Transaction::sale([
            'amount' => '100.00',
            'creditCard' => [
                'number' => '4023898493988028',
                'expirationDate' => '05/2009',
            ],
            'options' => [
                'skipAdvancedFraudChecking' => true
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

        $this->assertEquals(Braintree\Dispute::ACCEPTED, $dispute->status);
    }

    public function testAccept_errors_whenDisputeNotOpen()
    {
    }

    public function testAccept_raisesError_whenDisputeNotFound()
    {
    }

    public function testAddFileEvidence_addsEvidence()
    {
    }

    public function testAddFileEvidence_raisesError_whenDisputeNotFound()
    {
    }

    public function testAddFileEvidence_raisesError_whenDisputeNotOpen()
    {
    }

    public function testAddFileEvidence_returnsError_whenIncorrectDocumentKind()
    {
    }

    public function testAddTextEvidence_addsTextEvidence()
    {
    }

    public function testAddTextEvidence_raisesError_whenDisputeNotFound()
    {
    }

    public function testAddTextEvidence_raisesError_whenDisputeNotOpen()
    {
    }

    public function testAddTextEvidence_showsNewRecord_inFind()
    {
    }

    public function testFinalize_changesDisputeStatus_toDisputed()
    {
    }

    public function testFinalize_errors_whenDisputeNotOpen()
    {
    }

    public function testFinalize_raisesError_whenDisputeNotFound()
    {
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

    public function testtRemoveEvidence_removesEvidencFromTheDisupute()
    {
    }

    public function testtRemoveEvidence_raisesError_whenDisputeOrEvidenceNotFound()
    {
    }

    public function testtRemoveEvidence_errors_whenDisputeNotOpen()
    {
    }

    public function testUpdate_withValidationErrors()
    {
        // $customer = Braintree\Customer::createNoValidate();
        // $address = Braintree\Address::createNoValidate([
        //     'customerId' => $customer->id,
        //     'streetAddress' => '1 E Main St'
        // ]);
        // $result = Braintree\Address::update(
        //     $customer->id,
        //     $address->id,
        //     [
        //         'countryName' => 'Invalid States of America'
        //     ]
        // );
        // $this->assertFalse($result->success);
        // $countryErrors = $result->errors->forKey('address')->onAttribute('countryName');
        // $this->assertEquals(Braintree\Error\Codes::ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED, $countryErrors[0]->code);
    }

    public function testUpdate_withValidationErrors_onCountry()
    {
        // $customer = Braintree\Customer::createNoValidate();
        // $address = Braintree\Address::createNoValidate([
        //     'customerId' => $customer->id,
        //     'streetAddress' => '1 E Main St'
        // ]);
        // $result = Braintree\Address::update(
        //     $customer->id,
        //     $address->id,
        //     [
        //         'countryCodeAlpha2' => 'MU',
        //         'countryCodeAlpha3' => 'MYT'
        //     ]
        // );
        // $this->assertFalse($result->success);
        // $countryErrors = $result->errors->forKey('address')->onAttribute('base');
        // $this->assertEquals(Braintree\Error\Codes::ADDRESS_INCONSISTENT_COUNTRY, $countryErrors[0]->code);
    }


    public function testUpdateNoValidate()
    {
        // $customer = Braintree\Customer::createNoValidate();
        // $createdAddress = Braintree\Address::createNoValidate([
        //     'customerId' => $customer->id,
        //     'firstName' => 'Old First',
        //     'lastName' => 'Old Last',
        //     'company' => 'Old Company',
        //     'streetAddress' => '1 E Old St',
        //     'extendedAddress' => 'Apt Old',
        //     'locality' => 'Old Chicago',
        //     'region' => 'Old Region',
        //     'postalCode' => 'Old Postal',
        //     'countryName' => 'United States of America'
        // ]);
        // $address = Braintree\Address::updateNoValidate($customer->id, $createdAddress->id, [
        //     'firstName' => 'New First',
        //     'lastName' => 'New Last',
        //     'company' => 'New Company',
        //     'streetAddress' => '1 E New St',
        //     'extendedAddress' => 'Apt New',
        //     'locality' => 'New Chicago',
        //     'region' => 'New Region',
        //     'postalCode' => 'New Postal',
        //     'countryName' => 'Mexico'
        // ]);
        // $this->assertEquals('New First', $address->firstName);
        // $this->assertEquals('New Last', $address->lastName);
        // $this->assertEquals('New Company', $address->company);
        // $this->assertEquals('1 E New St', $address->streetAddress);
        // $this->assertEquals('Apt New', $address->extendedAddress);
        // $this->assertEquals('New Chicago', $address->locality);
        // $this->assertEquals('New Region', $address->region);
        // $this->assertEquals('New Postal', $address->postalCode);
        // $this->assertEquals('Mexico', $address->countryName);
    }
}
