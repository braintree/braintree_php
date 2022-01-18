<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class EnrichedCustomerDataTest extends Setup
{
    public function testFactory()
    {
        $enrichedCustomerData = Braintree\EnrichedCustomerData::factory([]);

        $this->assertInstanceOf('Braintree\EnrichedCustomerData', $enrichedCustomerData);
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

        $enrichedCustomerDataParams = [
            "fieldsUpdated" => array("username"),
            "profileData" => $profileDataParams 
        ];

        $enrichedCustomerData = Braintree\EnrichedCustomerData::factory($enrichedCustomerDataParams);

        $this->assertEquals("Braintree\EnrichedCustomerData[fieldsUpdated=0=username, profileData=Braintree\VenmoProfileData[username=venmo_username, firstName=John, lastName=Doe, phoneNumber=1231231234, email=john.doe@paypal.com]]", (string) $enrichedCustomerData);
    }
}


