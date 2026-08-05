<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class TransactionSearchTest extends Setup
{
    public function testSearch_achReturnResponsesCreatedAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::achReturnResponsesCreatedAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_acquirerReferenceNumber_isTextNode()
    {
        $node = Braintree\TransactionSearch::acquirerReferenceNumber();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_amount_isRangeNode()
    {
        $node = Braintree\TransactionSearch::amount();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_authorizationExpiredAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::authorizationExpiredAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_authorizedAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::authorizedAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_billingCompany_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingCompany();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingCountryName_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingCountryName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingExtendedAddress_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingExtendedAddress();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingFirstName_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingFirstName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingLastName_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingLastName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingLocality_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingLocality();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingPostalCode_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingPostalCode();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingRegion_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingRegion();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_billingStreetAddress_isTextNode()
    {
        $node = Braintree\TransactionSearch::billingStreetAddress();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_createdAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::createdAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_createdUsing_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::createdUsing();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_creditCardCardholderName_isTextNode()
    {
        $node = Braintree\TransactionSearch::creditCardCardholderName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_creditCardCardType_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::creditCardCardType();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_creditCardCustomerLocation_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::creditCardCustomerLocation();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_creditCardExpirationDate_isEqualityNode()
    {
        $node = Braintree\TransactionSearch::creditCardExpirationDate();
        $this->assertInstanceOf('Braintree\EqualityNode', $node);
    }

    public function testSearch_creditCardNumber_isPartialMatchNode()
    {
        $node = Braintree\TransactionSearch::creditCardNumber();
        $this->assertInstanceOf('Braintree\PartialMatchNode', $node);
    }

    public function testSearch_creditCardUniqueIdentifier_isTextNode()
    {
        $node = Braintree\TransactionSearch::creditCardUniqueIdentifier();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_currency_isTextNode()
    {
        $node = Braintree\TransactionSearch::currency();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerCompany_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerCompany();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerEmail_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerEmail();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerFax_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerFax();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerFirstName_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerFirstName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerId_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerLastName_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerLastName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerPhone_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerPhone();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_customerWebsite_isTextNode()
    {
        $node = Braintree\TransactionSearch::customerWebsite();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_debitNetwork_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::debitNetwork();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_disbursementDate_isRangeNode()
    {
        $node = Braintree\TransactionSearch::disbursementDate();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_disputeDate_isRangeNode()
    {
        $node = Braintree\TransactionSearch::disputeDate();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_failedAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::failedAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_gatewayRejectedAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::gatewayRejectedAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_id_isTextNode()
    {
        $node = Braintree\TransactionSearch::id();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_ids_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::ids();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_merchantAccountId_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::merchantAccountId();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_orderId_isTextNode()
    {
        $node = Braintree\TransactionSearch::orderId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paymentInstrumentType_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::paymentInstrumentType();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_paymentMethodToken_isTextNode()
    {
        $node = Braintree\TransactionSearch::paymentMethodToken();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paypalAuthorizationId_isTextNode()
    {
        $node = Braintree\TransactionSearch::paypalAuthorizationId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paypalPayerEmail_isTextNode()
    {
        $node = Braintree\TransactionSearch::paypalPayerEmail();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_paypalPaymentId_isTextNode()
    {
        $node = Braintree\TransactionSearch::paypalPaymentId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_processorAuthorizationCode_isTextNode()
    {
        $node = Braintree\TransactionSearch::processorAuthorizationCode();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_processorDeclinedAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::processorDeclinedAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_reasonCode_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::reasonCode();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_refund_isKeyValueNode()
    {
        $node = Braintree\TransactionSearch::refund();
        $this->assertInstanceOf('Braintree\KeyValueNode', $node);
    }

    public function testSearch_sepaDebitPaypalV2OrderId_isTextNode()
    {
        $node = Braintree\TransactionSearch::sepaDebitPaypalV2OrderId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_settledAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::settledAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_settlementBatchId_isTextNode()
    {
        $node = Braintree\TransactionSearch::settlementBatchId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingCompany_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingCompany();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingCountryName_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingCountryName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingExtendedAddress_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingExtendedAddress();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingFirstName_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingFirstName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingLastName_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingLastName();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingLocality_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingLocality();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingPostalCode_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingPostalCode();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingRegion_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingRegion();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_shippingStreetAddress_isTextNode()
    {
        $node = Braintree\TransactionSearch::shippingStreetAddress();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_submittedForSettlementAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::submittedForSettlementAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_storeId_isTextNode()
    {
        $node = Braintree\TransactionSearch::storeId();
        $this->assertInstanceOf('Braintree\TextNode', $node);
    }

    public function testSearch_storeIds_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::storeIds();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_user_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::user();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_voidedAt_isRangeNode()
    {
        $node = Braintree\TransactionSearch::voidedAt();
        $this->assertInstanceOf('Braintree\RangeNode', $node);
    }

    public function testSearch_source_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::source();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_status_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::status();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_type_isMultipleValueNode()
    {
        $node = Braintree\TransactionSearch::type();
        $this->assertInstanceOf('Braintree\MultipleValueNode', $node);
    }

    public function testSearch_status_allowsValidStatuses()
    {
        $node = Braintree\TransactionSearch::status();
        $collection = $node->in([
            Braintree\Transaction::AUTHORIZED,
            Braintree\Transaction::SETTLED
        ]);
        $this->assertEquals(
            [Braintree\Transaction::AUTHORIZED, Braintree\Transaction::SETTLED],
            $collection->toParam()
        );
    }

    public function testSearch_type_allowsValidTypes()
    {
        $node = Braintree\TransactionSearch::type();
        $collection = $node->is(Braintree\Transaction::SALE);
        $this->assertEquals([Braintree\Transaction::SALE], $collection->toParam());
    }
}
