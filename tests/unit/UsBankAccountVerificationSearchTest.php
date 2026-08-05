<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class UsBankAccountVerificationSearchTest extends Setup
{
    public function testSearch_accountHolderName_isTextNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::accountHolderName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerEmail_isTextNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::customerEmail();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerId_isTextNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::customerId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_id_isTextNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::id();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paymentMethodToken_isTextNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::paymentMethodToken();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_routingNumber_isTextNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::routingNumber();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_ids_isMultipleValueNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::ids();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_status_isMultipleValueNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::status();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_verificationMethod_isMultipleValueNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::verificationMethod();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_createdAt_isRangeNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::createdAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_accountType_isEqualityNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::accountType();
        $this->assertInstanceOf('Braintree\EqualityNode', $node);
    }

    public function testSearch_accountNumber_isEndsWithNode()
    {
        $node = Braintree\UsBankAccountVerificationSearch::accountNumber();
        $this->assertInstanceOf('Braintree\EndsWithNode', $node);
    }

    public function testSearch_status_allowsValidStatuses()
    {
        $node = Braintree\UsBankAccountVerificationSearch::status();
        $statuses = Braintree\Result\UsBankAccountVerification::allStatuses();
        $this->assertEquals($statuses, $node->in($statuses)->toParam());
    }

    public function testSearch_verificationMethod_allowsValidMethods()
    {
        $node = Braintree\UsBankAccountVerificationSearch::verificationMethod();
        $methods = Braintree\Result\UsBankAccountVerification::allVerificationMethods();
        $this->assertEquals($methods, $node->in($methods)->toParam());
    }
}
