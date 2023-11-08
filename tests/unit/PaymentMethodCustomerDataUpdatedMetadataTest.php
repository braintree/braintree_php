<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class PaymentMethodCustomerDataUpdatedMetadataTest extends Setup
{
    public function testFactory()
    {
        $paymentMethodCustomerDataUpdatedMetadata = Braintree\PaymentMethodCustomerDataUpdatedMetadata::factory([]);

        $this->assertInstanceOf('Braintree\PaymentMethodCustomerDataUpdatedMetadata', $paymentMethodCustomerDataUpdatedMetadata);
    }

    public function testToString()
    {
        $address = [
            "streetAddress" => "a-street-address",
            "extendedAddress" => "an-extended-address",
            "locality" => "a-locality",
            "region" => "a-region",
            "postalCode" => "a-code"
        ];

        $profileDataParams = [
            "username" => "venmo_username",
            "firstName" => "John",
            "lastName" => "Doe",
            "phoneNumber" => "1231231234",
            "email" => "john.doe@paypal.com",
            "billingAddress" => $address,
            "shippingAddress" => $address,
        ];

        $enrichedCustomerDataParams = [
            "fieldsUpdated" => array("username"),
            "profileData" => $profileDataParams,
        ];

        $venmoAccountParams = [
            "createdAt" => "2018-10-11T21:28:37Z",
            "updatedAt" => "2018-10-11T21:28:37Z",
            "default" => true,
            "imageUrl" => "https://assets.braintreegateway.com/payment_method_logo/mastercard.png?environment=test",
            "token" => "venmo_account",
            "sourceDescription" => "Venmo Account: venmojoe",
            "username" => "venmojoe",
            "venmoUserId" => "456",
            "subscriptions" => array(),
            "customerId" => "venmo_customer_id",
            "globalId" => "cGF5bWVudG1ldGhvZF92ZW5tb2FjY291bnQ",
        ];

        $venmoAccount = [
            "venmoAccount" => $venmoAccountParams,
        ];

        $paymentMethodParams = [
            "paymentMethod" => $venmoAccount,
        ];

        $paymentMethodCustomerDataUpdatedMetadataParams = [
            "token" => "TOKEN-12345",
            "paymentMethod" => $paymentMethodParams,
            "datetimeUpdated" => "2022-01-01T21:28:37Z",
            "enrichedCustomerData" => $enrichedCustomerDataParams,
        ];

        $paymentMethodCustomerDataUpdatedMetadata = Braintree\PaymentMethodCustomerDataUpdatedMetadata::factory($paymentMethodCustomerDataUpdatedMetadataParams);

        $this->assertEquals('Braintree\PaymentMethodCustomerDataUpdatedMetadata[token=TOKEN-12345, paymentMethod=paymentMethod=venmoAccount=createdAt=2018-10-11T21:28:37Z, updatedAt=2018-10-11T21:28:37Z, default=1, imageUrl=https://assets.braintreegateway.com/payment_method_logo/mastercard.png?environment=test, token=venmo_account, sourceDescription=Venmo Account: venmojoe, username=venmojoe, venmoUserId=456, subscriptions=, customerId=venmo_customer_id, globalId=cGF5bWVudG1ldGhvZF92ZW5tb2FjY291bnQ, datetimeUpdated=2022-01-01T21:28:37Z, enrichedCustomerData=Braintree\EnrichedCustomerData[fieldsUpdated=0=username, profileData=Braintree\VenmoProfileData[username=venmo_username, firstName=John, lastName=Doe, phoneNumber=1231231234, email=john.doe@paypal.com, billingAddress=Braintree\Address[streetAddress=a-street-address, extendedAddress=an-extended-address, locality=a-locality, region=a-region, postalCode=a-code], shippingAddress=Braintree\Address[streetAddress=a-street-address, extendedAddress=an-extended-address, locality=a-locality, region=a-region, postalCode=a-code]]]]', (string) $paymentMethodCustomerDataUpdatedMetadata);
    }
}
