<?php

namespace Braintree\Test;

/**
 * VenmoSdk payment method codes used for testing purposes
 *
 * @package    Braintree
 * @subpackage Test
 * @copyright  2010 Braintree Payment Solutions
 */
class VenmoSdk
{
    public static $visaPaymentMethodCode = "stub-4111111111111111";

    public static function generateTestPaymentMethodCode($number) {
        return "stub-" . $number;
    }

    public static function getInvalidPaymentMethodCode() {
        return "stub-invalid-payment-method-code";
    }

    public static function getTestSession() {
        return "stub-session";
    }

    public static function getInvalidTestSession() {
        return "stub-invalid-session";
    }
}
