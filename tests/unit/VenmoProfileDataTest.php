<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class VenmoProfileDataTest extends Setup
{
    public function testFactory()
    {
        $profileData = Braintree\VenmoProfileData::factory([]);

        $this->assertInstanceOf('Braintree\VenmoProfileData', $profileData);
    }

    public function testToString()
    {
        $address = [
            "streetAddress" => "a-street-address",
            "extendedAddress" => "an-extended-address",
            "locality" => "a-locality",
            "region" => "a-region",
            "postalCode" => "a-code",
        ];

        $profileDataParams = [
            "username" => "venmo_username",
            "firstName" => "John",
            "lastName" => "Doe",
            "phoneNumber" => "1231231234",
            "email" => "john.doe@paypal.com",
            "billingAddress" => $address,
            "shippingAddress" => $address
        ];

        $profileData = Braintree\VenmoProfileData::factory($profileDataParams);

        $this->assertEquals("Braintree\VenmoProfileData[username=venmo_username, firstName=John, lastName=Doe, phoneNumber=1231231234, email=john.doe@paypal.com, billingAddress=Braintree\Address[streetAddress=a-street-address, extendedAddress=an-extended-address, locality=a-locality, region=a-region, postalCode=a-code], shippingAddress=Braintree\Address[streetAddress=a-street-address, extendedAddress=an-extended-address, locality=a-locality, region=a-region, postalCode=a-code]]", (string) $profileData);
    }
}
