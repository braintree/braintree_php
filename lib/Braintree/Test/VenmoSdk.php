<?php

# NEXT_MAJOR_VERSION Remove this class
# The old venmo SDK class has been deprecated
namespace Braintree\Test;

/**
 * VenmoSdk payment method codes used for testing purposes
 */
class VenmoSdk
{
    public static $visaPaymentMethodCode = "stub-4111111111111111";

    public static function generateTestPaymentMethodCode($number)
    {
        return "stub-" . $number;
    }

    public static function getInvalidPaymentMethodCode()
    {
        return "stub-invalid-payment-method-code";
    }

    public static function getTestSession()
    {
        return "stub-session";
    }

    public static function getInvalidTestSession()
    {
        return "stub-invalid-session";
    }
}
