<?php

namespace Test\Braintree\CreditCardNumbers;

class CardTypeIndicators
{
    const PREPAID             = "4111111111111210";
    const PREPAID_RELOADABLE  = "4229989900000002";
    const BUSINESS            = "4229989800000003";
    const COMMERCIAL          = "4111111111131010";
    const CONSUMER            = "4229989700000004";
    const CORPORATE           = "4229989100000000";
    const PAYROLL             = "4111111114101010";
    const PURCHASE            = "4229989500000006";
    const HEALTHCARE          = "4111111510101010";
    const DURBIN_REGULATED    = "4111161010101010";
    const DEBIT               = "4117101010101010";
    const UNKNOWN             = "4111111111112101";
    const NO                  = "4111111111310101";
    const ISSUING_BANK        = "4111111141010101";
    const COUNTRY_OF_ISSUANCE = "4111111111121102";
}
