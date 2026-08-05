<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class CreditCardVerificationSearchTest extends Setup
{
    public function testSearch_id_isTextNode()
    {
        $node = Braintree\CreditCardVerificationSearch::id();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_creditCardCardholderName_isTextNode()
    {
        $node = Braintree\CreditCardVerificationSearch::creditCardCardholderName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingAddressDetailsPostalCode_isTextNode()
    {
        $node = Braintree\CreditCardVerificationSearch::billingAddressDetailsPostalCode();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerEmail_isTextNode()
    {
        $node = Braintree\CreditCardVerificationSearch::customerEmail();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerId_isTextNode()
    {
        $node = Braintree\CreditCardVerificationSearch::customerId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paymentMethodToken_isTextNode()
    {
        $node = Braintree\CreditCardVerificationSearch::paymentMethodToken();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_creditCardExpirationDate_isEqualityNode()
    {
        $node = Braintree\CreditCardVerificationSearch::creditCardExpirationDate();
        $this->assertInstanceOf('Braintree\EqualityNode', $node);
    }

    public function testSearch_creditCardNumber_isPartialMatchNode()
    {
        $node = Braintree\CreditCardVerificationSearch::creditCardNumber();
        $this->assertInstanceOf('Braintree\PartialMatchNode', $node);
    }

    public function testSearch_ids_isMultipleValueNode()
    {
        $node = Braintree\CreditCardVerificationSearch::ids();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_createdAt_isRangeNode()
    {
        $node = Braintree\CreditCardVerificationSearch::createdAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_creditCardCardType_isMultipleValueNode()
    {
        $node = Braintree\CreditCardVerificationSearch::creditCardCardType();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_status_isMultipleValueNode()
    {
        $node = Braintree\CreditCardVerificationSearch::status();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_creditCardCardType_allowsValidCardTypes()
    {
        $node = Braintree\CreditCardVerificationSearch::creditCardCardType();
        $cardTypes = Braintree\CreditCard::allCardTypes();
        $this->assertEquals($cardTypes, $node->in($cardTypes)->toParam());
    }

    public function testSearch_status_allowsValidStatuses()
    {
        $node = Braintree\CreditCardVerificationSearch::status();
        $statuses = Braintree\Result\CreditCardVerification::allStatuses();
        $this->assertEquals($statuses, $node->in($statuses)->toParam());
    }
}
