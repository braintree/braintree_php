<?php

namespace Braintree;

/**
 * Braintree PaymentMethodParser module
 *
 * @package    Braintree
 * @category   Resources
 */

/**
 * Manages Braintree PaymentMethodParser
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 *
 */
class PaymentMethodParser
{
    public static function parsePaymentMethod($response)
    {
        if (isset($response['creditCard'])) {
            return CreditCard::factory($response['creditCard']);
        } elseif (isset($response['paypalAccount'])) {
            return PayPalAccount::factory($response['paypalAccount']);
        } elseif (isset($response['applePayCard'])) {
            return ApplePayCard::factory($response['applePayCard']);
        } elseif (isset($response['androidPayCard'])) {
            return GooglePayCard::factory($response['androidPayCard']);
        } elseif (isset($response['usBankAccount'])) {
            return UsBankAccount::factory($response['usBankAccount']);
        } elseif (isset($response['venmoAccount'])) {
            return VenmoAccount::factory($response['venmoAccount']);
        } elseif (isset($response['visaCheckoutCard'])) {
            return VisaCheckoutCard::factory($response['visaCheckoutCard']);
        } elseif (isset($response['samsungPayCard'])) {
            return SamsungPayCard::factory($response['samsungPayCard']);
        } elseif (is_array($response)) {
            return UnknownPaymentMethod::factory($response);
        } else {
            throw new Exception\Unexpected(
                'Expected payment method'
            );
        }
    }
}
