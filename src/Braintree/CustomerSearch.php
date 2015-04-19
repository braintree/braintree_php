<?php namespace Braintree;

class CustomerSearch
{
    static function addressCountryName()               { return new TextNode('address_country_name'); }
    static function addressExtendedAddress()           { return new TextNode('address_extended_address'); }
    static function addressFirstName()                 { return new TextNode('address_first_name'); }
    static function addressLastName()                  { return new TextNode('address_last_name'); }
    static function addressLocality()                  { return new TextNode('address_locality'); }
    static function addressPostalCode()                { return new TextNode('address_postal_code'); }
    static function addressRegion()                    { return new TextNode('address_region'); }
    static function addressStreetAddress()             { return new TextNode('address_street_address'); }
    static function cardholderName()                   { return new TextNode('cardholder_name'); }
    static function company()                          { return new TextNode('company'); }
    static function email()                            { return new TextNode('email'); }
    static function fax()                              { return new TextNode('fax'); }
    static function firstName()                        { return new TextNode('first_name'); }
    static function id()                               { return new TextNode('id'); }
    static function lastName()                         { return new TextNode('last_name'); }
    static function paymentMethodToken()               { return new TextNode('payment_method_token'); }
    static function paymentMethodTokenWithDuplicates() { return new IsNode('payment_method_token_with_duplicates'); }
    static function paypalAccountEmail()               { return new IsNode('paypal_account_email'); }
    static function phone()                            { return new TextNode('phone'); }
    static function website()                          { return new TextNode('website'); }

    static function creditCardExpirationDate()         { return new EqualityNode('credit_card_expiration_date'); }
    static function creditCardNumber()                 { return new PartialMatchNode('credit_card_number'); }

    static function ids()                              { return new MultipleValueNode('ids'); }

    static function createdAt()                        { return new RangeNode("created_at"); }
}
