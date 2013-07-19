<?php

namespace Braintree;

class CreditCardVerificationSearch
{
    static function id()                               { return new TextNode('id'); }
    static function creditCardCardholderName()                   { return new TextNode('credit_card_cardholder_name'); }

    static function creditCardExpirationDate()         { return new EqualityNode('credit_card_expiration_date'); }
    static function creditCardNumber()                 { return new PartialMatchNode('credit_card_number'); }

    static function ids()                              { return new MultipleValueNode('ids'); }

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


    static function createdAt()                        { return new RangeNode("created_at"); }
}

