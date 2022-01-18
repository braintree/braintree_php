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
        $profileDataParams = [
            "username" => "venmo_username",
            "firstName" => "John",
            "lastName" => "Doe",
            "phoneNumber" => "1231231234",
            "email" => "john.doe@paypal.com",
        ];

        $profileData = Braintree\VenmoProfileData::factory($profileDataParams);
        var_dump($profileData);

        $this->assertEquals("Braintree\VenmoProfileData[username=venmo_username, firstName=John, lastName=Doe, phoneNumber=1231231234, email=john.doe@paypal.com]", (string) $profileData);
    }
}

