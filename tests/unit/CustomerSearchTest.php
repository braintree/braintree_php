<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class CustomerSearchTest extends Setup
{
    public function testSearch_addressCountryName_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressCountryName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressExtendedAddress_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressExtendedAddress();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressFirstName_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressFirstName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressLastName_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressLastName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressLocality_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressLocality();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressPostalCode_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressPostalCode();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressRegion_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressRegion();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_addressStreetAddress_isTextNode()
    {
        $node = Braintree\CustomerSearch::addressStreetAddress();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_cardholderName_isTextNode()
    {
        $node = Braintree\CustomerSearch::cardholderName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_company_isTextNode()
    {
        $node = Braintree\CustomerSearch::company();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_email_isTextNode()
    {
        $node = Braintree\CustomerSearch::email();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_fax_isTextNode()
    {
        $node = Braintree\CustomerSearch::fax();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_firstName_isTextNode()
    {
        $node = Braintree\CustomerSearch::firstName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_id_isTextNode()
    {
        $node = Braintree\CustomerSearch::id();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_lastName_isTextNode()
    {
        $node = Braintree\CustomerSearch::lastName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paymentMethodToken_isTextNode()
    {
        $node = Braintree\CustomerSearch::paymentMethodToken();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paymentMethodTokenWithDuplicates_isIsNode()
    {
        $node = Braintree\CustomerSearch::paymentMethodTokenWithDuplicates();
        $this->assertInstanceOf('Braintree\IsNode', $node);
    }

    public function testSearch_paypalAccountEmail_isIsNode()
    {
        $node = Braintree\CustomerSearch::paypalAccountEmail();
        $this->assertInstanceOf('Braintree\IsNode', $node);
    }

    public function testSearch_phone_isTextNode()
    {
        $node = Braintree\CustomerSearch::phone();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_website_isTextNode()
    {
        $node = Braintree\CustomerSearch::website();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_creditCardExpirationDate_isEqualityNode()
    {
        $node = Braintree\CustomerSearch::creditCardExpirationDate();
        $this->assertInstanceOf('Braintree\EqualityNode', $node);
    }

    public function testSearch_creditCardNumber_isPartialMatchNode()
    {
        $node = Braintree\CustomerSearch::creditCardNumber();
        $this->assertInstanceOf('Braintree\PartialMatchNode', $node);
    }

    public function testSearch_ids_isMultipleValueNode()
    {
        $node = Braintree\CustomerSearch::ids();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_createdAt_isRangeNode()
    {
        $node = Braintree\CustomerSearch::createdAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }
}
