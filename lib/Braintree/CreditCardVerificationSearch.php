<?php namespace Braintree;

class CreditCardVerificationSearch
{
    static function id()
    {
        return new TextNode('id');
    }

    static function creditCardCardholderName()
    {
        return new TextNode('credit_card_cardholder_name');
    }

    static function billingAddressDetailsPostalCode()
    {
        return new TextNode('billing_address_details_postal_code');
    }

    static function customerEmail()
    {
        return new TextNode('customer_email');
    }

    static function customerId()
    {
        return new TextNode('customer_id');
    }

    static function paymentMethodToken()
    {
        return new TextNode('payment_method_token');
    }

    static function creditCardExpirationDate()
    {
        return new EqualityNode('credit_card_expiration_date');
    }

    static function creditCardNumber()
    {
        return new PartialMatchNode('credit_card_number');
    }

    static function ids()
    {
        return new MultipleValueNode('ids');
    }

    static function createdAt()
    {
        return new RangeNode("created_at");
    }

    static function creditCardCardType()
    {
        return new MultipleValueNode("credit_card_card_type", CreditCard::allCardTypes());
    }

    static function status()
    {
        return new MultipleValueNode("status", CreditCardVerification::allStatuses());
    }
}
