<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PaymentMethodParserTest extends Setup
{
    public function testParsePaymentMethod_returnsCreditCard()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'creditCard' => ['token' => 'tok_1', 'last4' => '1111'],
        ]);

        $this->assertInstanceOf('Braintree\CreditCard', $result);
    }

    public function testParsePaymentMethod_returnsPayPalAccount()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'paypalAccount' => ['token' => 'tok_2', 'email' => 'user@example.com'],
        ]);

        $this->assertInstanceOf('Braintree\PayPalAccount', $result);
    }

    public function testParsePaymentMethod_returnsApplePayCard()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'applePayCard' => ['token' => 'tok_3'],
        ]);

        $this->assertInstanceOf('Braintree\ApplePayCard', $result);
    }

    public function testParsePaymentMethod_returnsGooglePayCardForAndroidPayCard()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'androidPayCard' => [
                'token' => 'tok_4',
                'virtualCardLast4' => '1234',
                'virtualCardType' => 'Visa',
            ],
        ]);

        $this->assertInstanceOf('Braintree\GooglePayCard', $result);
    }

    public function testParsePaymentMethod_returnsUsBankAccount()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'usBankAccount' => ['token' => 'tok_5', 'last4' => '6789'],
        ]);

        $this->assertInstanceOf('Braintree\UsBankAccount', $result);
    }

    public function testParsePaymentMethod_returnsVenmoAccount()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'venmoAccount' => ['token' => 'tok_6', 'username' => 'venmo_user'],
        ]);

        $this->assertInstanceOf('Braintree\VenmoAccount', $result);
    }

    public function testParsePaymentMethod_returnsSepaDirectDebitAccount()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'sepaDebitAccount' => ['token' => 'tok_7', 'last4' => '1234'],
        ]);

        $this->assertInstanceOf('Braintree\SepaDirectDebitAccount', $result);
    }

    public function testParsePaymentMethod_returnsUnknownPaymentMethodForEmptyArray()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([]);

        $this->assertInstanceOf('Braintree\UnknownPaymentMethod', $result);
    }

    // NEXT_MAJOR_VERSION remove when VisaCheckoutCard is removed
    public function testParsePaymentMethod_returnsVisaCheckoutCard()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'visaCheckoutCard' => ['token' => 'tok_8', 'callId' => 'call_123'],
        ]);

        $this->assertInstanceOf('Braintree\VisaCheckoutCard', $result);
    }

    // NEXT_MAJOR_VERSION remove when SamsungPayCard is removed
    public function testParsePaymentMethod_returnsSamsungPayCard()
    {
        $result = Braintree\PaymentMethodParser::parsePaymentMethod([
            'samsungPayCard' => [
                'token' => 'tok_9',
                'sourceCardLast4' => '1111',
                'virtualCardLast4' => '4444',
                'expirationMonth' => '01',
                'expirationYear' => '2025',
            ],
        ]);

        $this->assertInstanceOf('Braintree\SamsungPayCard', $result);
    }

    public function testParsePaymentMethod_throwsExceptionForNonArray()
    {
        $this->expectException('Braintree\Exception\Unexpected');
        $this->expectExceptionMessage('Expected payment method');
        Braintree\PaymentMethodParser::parsePaymentMethod('invalid');
    }
}
