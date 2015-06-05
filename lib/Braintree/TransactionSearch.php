<?php namespace Braintree;

class TransactionSearch
{
    static function billingCompany()
    {
        return new TextNode('billing_company');
    }

    static function billingCountryName()
    {
        return new TextNode('billing_country_name');
    }

    static function billingExtendedAddress()
    {
        return new TextNode('billing_extended_address');
    }

    static function billingFirstName()
    {
        return new TextNode('billing_first_name');
    }

    static function billingLastName()
    {
        return new TextNode('billing_last_name');
    }

    static function billingLocality()
    {
        return new TextNode('billing_locality');
    }

    static function billingPostalCode()
    {
        return new TextNode('billing_postal_code');
    }

    static function billingRegion()
    {
        return new TextNode('billing_region');
    }

    static function billingStreetAddress()
    {
        return new TextNode('billing_street_address');
    }

    static function creditCardCardholderName()
    {
        return new TextNode('credit_card_cardholderName');
    }

    static function customerCompany()
    {
        return new TextNode('customer_company');
    }

    static function customerEmail()
    {
        return new TextNode('customer_email');
    }

    static function customerFax()
    {
        return new TextNode('customer_fax');
    }

    static function customerFirstName()
    {
        return new TextNode('customer_first_name');
    }

    static function customerId()
    {
        return new TextNode('customer_id');
    }

    static function customerLastName()
    {
        return new TextNode('customer_last_name');
    }

    static function customerPhone()
    {
        return new TextNode('customer_phone');
    }

    static function customerWebsite()
    {
        return new TextNode('customer_website');
    }

    static function id()
    {
        return new TextNode('id');
    }

    static function ids()
    {
        return new MultipleValueNode('ids');
    }

    static function orderId()
    {
        return new TextNode('order_id');
    }

    static function paymentMethodToken()
    {
        return new TextNode('payment_method_token');
    }

    static function processorAuthorizationCode()
    {
        return new TextNode('processor_authorization_code');
    }

    static function settlementBatchId()
    {
        return new TextNode('settlement_batch_id');
    }

    static function shippingCompany()
    {
        return new TextNode('shipping_company');
    }

    static function shippingCountryName()
    {
        return new TextNode('shipping_country_name');
    }

    static function shippingExtendedAddress()
    {
        return new TextNode('shipping_extended_address');
    }

    static function shippingFirstName()
    {
        return new TextNode('shipping_first_name');
    }

    static function shippingLastName()
    {
        return new TextNode('shipping_last_name');
    }

    static function shippingLocality()
    {
        return new TextNode('shipping_locality');
    }

    static function shippingPostalCode()
    {
        return new TextNode('shipping_postal_code');
    }

    static function shippingRegion()
    {
        return new TextNode('shipping_region');
    }

    static function shippingStreetAddress()
    {
        return new TextNode('shipping_street_address');
    }

    static function paypalPaymentId()
    {
        return new TextNode('paypal_payment_id');
    }

    static function paypalAuthorizationId()
    {
        return new TextNode('paypal_authorization_id');
    }

    static function paypalPayerEmail()
    {
        return new TextNode('paypal_payer_email');
    }

    static function creditCardExpirationDate()
    {
        return new EqualityNode('credit_card_expiration_date');
    }

    static function creditCardNumber()
    {
        return new PartialMatchNode('credit_card_number');
    }

    static function refund()
    {
        return new KeyValueNode("refund");
    }

    static function amount()
    {
        return new RangeNode("amount");
    }

    static function authorizedAt()
    {
        return new RangeNode("authorizedAt");
    }

    static function authorizationExpiredAt()
    {
        return new RangeNode("authorizationExpiredAt");
    }

    static function createdAt()
    {
        return new RangeNode("createdAt");
    }

    static function failedAt()
    {
        return new RangeNode("failedAt");
    }

    static function gatewayRejectedAt()
    {
        return new RangeNode("gatewayRejectedAt");
    }

    static function processorDeclinedAt()
    {
        return new RangeNode("processorDeclinedAt");
    }

    static function settledAt()
    {
        return new RangeNode("settledAt");
    }

    static function submittedForSettlementAt()
    {
        return new RangeNode("submittedForSettlementAt");
    }

    static function voidedAt()
    {
        return new RangeNode("voidedAt");
    }

    static function disbursementDate()
    {
        return new RangeNode("disbursementDate");
    }

    static function disputeDate()
    {
        return new RangeNode("disputeDate");
    }

    static function merchantAccountId()
    {
        return new MultipleValueNode("merchant_account_id");
    }

    static function createdUsing()
    {
        return new MultipleValueNode("created_using", array(
            Transaction::FULL_INFORMATION,
            Transaction::TOKEN
        ));
    }

    static function creditCardCardType()
    {
        return new MultipleValueNode("credit_card_card_type", array(
            CreditCard::AMEX,
            CreditCard::CARTE_BLANCHE,
            CreditCard::CHINA_UNION_PAY,
            CreditCard::DINERS_CLUB_INTERNATIONAL,
            CreditCard::DISCOVER,
            CreditCard::JCB,
            CreditCard::LASER,
            CreditCard::MAESTRO,
            CreditCard::MASTER_CARD,
            CreditCard::SOLO,
            CreditCard::SWITCH_TYPE,
            CreditCard::VISA,
            CreditCard::UNKNOWN
        ));
    }

    static function creditCardCustomerLocation()
    {
        return new MultipleValueNode("credit_card_customer_location", array(
            CreditCard::INTERNATIONAL,
            CreditCard::US
        ));
    }

    static function source()
    {
        return new MultipleValueNode("source", array(
            Transaction::API,
            Transaction::CONTROL_PANEL,
            Transaction::RECURRING,
        ));
    }

    static function status()
    {
        return new MultipleValueNode("status", array(
            Transaction::AUTHORIZATION_EXPIRED,
            Transaction::AUTHORIZING,
            Transaction::AUTHORIZED,
            Transaction::GATEWAY_REJECTED,
            Transaction::FAILED,
            Transaction::PROCESSOR_DECLINED,
            Transaction::SETTLED,
            Transaction::SETTLING,
            Transaction::SUBMITTED_FOR_SETTLEMENT,
            Transaction::VOIDED,
            Transaction::SETTLEMENT_DECLINED,
            Transaction::SETTLEMENT_PENDING
        ));
    }

    static function type()
    {
        return new MultipleValueNode("type", array(
            Transaction::SALE,
            Transaction::CREDIT
        ));
    }
}
