<?php

namespace Test\Unit\GraphQL;

use Braintree\GraphQL\Inputs\PhoneInput;
use PHPUnit\Framework\TestCase;

class PhoneInputTest extends TestCase
{
    public function testBuilder()
    {
        $phoneInput = PhoneInput::builder()
            ->countryPhoneCode('1')
            ->phoneNumber('5551234567')
            ->extensionNumber('1234')
            ->build();

        $this->assertInstanceOf('Braintree\GraphQL\Inputs\PhoneInput', $phoneInput);
    }

    public function testToString()
    {
        $phoneInput = PhoneInput::builder()
            ->countryPhoneCode('1')
            ->phoneNumber('5551234567')
            ->extensionNumber('1234')
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\PhoneInput[countryPhoneCode=1, phoneNumber=5551234567, extensionNumber=1234]";

        $this->assertEquals($expectedString, (string) $phoneInput);
    }

    public function testToStringWithNullValues()
    {
        $phoneInput = PhoneInput::builder()
            ->build();

        $expectedString = "Braintree\GraphQL\Inputs\PhoneInput[]";

        $this->assertEquals($expectedString, (string) $phoneInput);
    }

    public function testToArray()
    {
        $phoneInput = PhoneInput::builder()
            ->countryPhoneCode('1')
            ->phoneNumber('5551234567')
            ->extensionNumber('1234')
            ->build();

        $expectedArray = [
            'countryPhoneCode' => '1',
            'phoneNumber' => '5551234567',
            'extensionNumber' => '1234',
        ];

        $this->assertEquals($expectedArray, $phoneInput->toArray());
    }

    public function testToArrayWithNullValues()
    {
        $phoneInput = PhoneInput::builder()
            ->build();

        $expectedArray = [];

        $this->assertEquals($expectedArray, $phoneInput->toArray());
    }
}
