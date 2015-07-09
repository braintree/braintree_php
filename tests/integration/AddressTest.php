<?php namespace Braintree\Tests\Integration;

use Braintree\Address;
use Braintree\Customer;
use Braintree\Error\Codes;
use Braintree\Gateway;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class AddressTest extends \PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $customer = Customer::createNoValidate();
        $result = Address::create(array(
            'customerId'         => $customer->id,
            'firstName'          => 'Dan',
            'lastName'           => 'Smith',
            'company'            => 'Braintree',
            'streetAddress'      => '1 E Main St',
            'extendedAddress'    => 'Apt 1F',
            'locality'           => 'Chicago',
            'region'             => 'IL',
            'postalCode'         => '60622',
            'countryName'        => 'Vatican City',
            'countryCodeAlpha2'  => 'VA',
            'countryCodeAlpha3'  => 'VAT',
            'countryCodeNumeric' => '336'
        ));
        $this->assertTrue($result->success);
        $address = $result->address;
        $this->assertEquals('Dan', $address->firstName);
        $this->assertEquals('Smith', $address->lastName);
        $this->assertEquals('Braintree', $address->company);
        $this->assertEquals('1 E Main St', $address->streetAddress);
        $this->assertEquals('Apt 1F', $address->extendedAddress);
        $this->assertEquals('Chicago', $address->locality);
        $this->assertEquals('IL', $address->region);
        $this->assertEquals('60622', $address->postalCode);
        $this->assertEquals('Vatican City', $address->countryName);
        $this->assertEquals('VA', $address->countryCodeAlpha2);
        $this->assertEquals('VAT', $address->countryCodeAlpha3);
        $this->assertEquals('336', $address->countryCodeNumeric);
    }

    function testGatewayCreate()
    {
        $customer = Customer::createNoValidate();

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $result = $gateway->address()->create(array(
            'customerId'    => $customer->id,
            'streetAddress' => '1 E Main St',
            'locality'      => 'Chicago',
            'region'        => 'IL',
            'postalCode'    => '60622',
        ));

        $this->assertTrue($result->success);
        $address = $result->address;
        $this->assertEquals('1 E Main St', $address->streetAddress);
        $this->assertEquals('Chicago', $address->locality);
        $this->assertEquals('IL', $address->region);
        $this->assertEquals('60622', $address->postalCode);
    }

    function testCreate_withValidationErrors()
    {
        $customer = Customer::createNoValidate();
        $result = Address::create(array(
            'customerId'  => $customer->id,
            'countryName' => 'Invalid States of America'
        ));
        $this->assertFalse($result->success);
        $countryErrors = $result->errors->forKey('address')->onAttribute('countryName');
        $this->assertEquals(Codes::ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED, $countryErrors[0]->code);
    }

    function testCreate_withValidationErrors_onCountryCodes()
    {
        $customer = Customer::createNoValidate();
        $result = Address::create(array(
            'customerId'        => $customer->id,
            'countryCodeAlpha2' => 'ZZ'
        ));
        $this->assertFalse($result->success);
        $countryErrors = $result->errors->forKey('address')->onAttribute('countryCodeAlpha2');
        $this->assertEquals(Codes::ADDRESS_COUNTRY_CODE_ALPHA2_IS_NOT_ACCEPTED, $countryErrors[0]->code);
    }

    function testCreateNoValidate()
    {
        $customer = Customer::createNoValidate();
        $address = Address::createNoValidate(array(
            'customerId'      => $customer->id,
            'firstName'       => 'Dan',
            'lastName'        => 'Smith',
            'company'         => 'Braintree',
            'streetAddress'   => '1 E Main St',
            'extendedAddress' => 'Apt 1F',
            'locality'        => 'Chicago',
            'region'          => 'IL',
            'postalCode'      => '60622',
            'countryName'     => 'United States of America'
        ));
        $this->assertEquals('Dan', $address->firstName);
        $this->assertEquals('Smith', $address->lastName);
        $this->assertEquals('Braintree', $address->company);
        $this->assertEquals('1 E Main St', $address->streetAddress);
        $this->assertEquals('Apt 1F', $address->extendedAddress);
        $this->assertEquals('Chicago', $address->locality);
        $this->assertEquals('IL', $address->region);
        $this->assertEquals('60622', $address->postalCode);
        $this->assertEquals('United States of America', $address->countryName);
    }

    function testCreateNoValidate_withValidationErrors()
    {
        $customer = Customer::createNoValidate();
        $this->setExpectedException('Braintree\Exception\ValidationsFailed');
        Address::createNoValidate(array(
            'customerId'  => $customer->id,
            'countryName' => 'Invalid States of America'
        ));
    }

    function testDelete()
    {
        $customer = Customer::createNoValidate();
        $address = Address::createNoValidate(array(
            'customerId'    => $customer->id,
            'streetAddress' => '1 E Main St'
        ));
        Address::find($customer->id, $address->id);
        Address::delete($customer->id, $address->id);
        $this->setExpectedException('Braintree\Exception\NotFound');
        Address::find($customer->id, $address->id);
    }

    function testFind()
    {
        $customer = Customer::createNoValidate();
        $result = Address::create(array(
            'customerId'      => $customer->id,
            'firstName'       => 'Dan',
            'lastName'        => 'Smith',
            'company'         => 'Braintree',
            'streetAddress'   => '1 E Main St',
            'extendedAddress' => 'Apt 1F',
            'locality'        => 'Chicago',
            'region'          => 'IL',
            'postalCode'      => '60622',
            'countryName'     => 'United States of America'
        ));
        $this->assertTrue($result->success);
        $address = Address::find($customer->id, $result->address->id);
        $this->assertEquals('Dan', $address->firstName);
        $this->assertEquals('Smith', $address->lastName);
        $this->assertEquals('Braintree', $address->company);
        $this->assertEquals('1 E Main St', $address->streetAddress);
        $this->assertEquals('Apt 1F', $address->extendedAddress);
        $this->assertEquals('Chicago', $address->locality);
        $this->assertEquals('IL', $address->region);
        $this->assertEquals('60622', $address->postalCode);
        $this->assertEquals('United States of America', $address->countryName);
    }

    function testFind_whenNotFound()
    {
        $customer = Customer::createNoValidate();
        $this->setExpectedException('Braintree\Exception\NotFound');
        Address::find($customer->id, 'does-not-exist');
    }

    function testUpdate()
    {
        $customer = Customer::createNoValidate();
        $address = Address::createNoValidate(array(
            'customerId'         => $customer->id,
            'firstName'          => 'Old First',
            'lastName'           => 'Old Last',
            'company'            => 'Old Company',
            'streetAddress'      => '1 E Old St',
            'extendedAddress'    => 'Apt Old',
            'locality'           => 'Old Chicago',
            'region'             => 'Old Region',
            'postalCode'         => 'Old Postal',
            'countryName'        => 'United States of America',
            'countryCodeAlpha2'  => 'US',
            'countryCodeAlpha3'  => 'USA',
            'countryCodeNumeric' => '840'
        ));
        $result = Address::update($customer->id, $address->id, array(
            'firstName'          => 'New First',
            'lastName'           => 'New Last',
            'company'            => 'New Company',
            'streetAddress'      => '1 E New St',
            'extendedAddress'    => 'Apt New',
            'locality'           => 'New Chicago',
            'region'             => 'New Region',
            'postalCode'         => 'New Postal',
            'countryName'        => 'Mexico',
            'countryCodeAlpha2'  => 'MX',
            'countryCodeAlpha3'  => 'MEX',
            'countryCodeNumeric' => '484'
        ));
        $this->assertTrue($result->success);
        $address = $result->address;
        $this->assertEquals('New First', $address->firstName);
        $this->assertEquals('New Last', $address->lastName);
        $this->assertEquals('New Company', $address->company);
        $this->assertEquals('1 E New St', $address->streetAddress);
        $this->assertEquals('Apt New', $address->extendedAddress);
        $this->assertEquals('New Chicago', $address->locality);
        $this->assertEquals('New Region', $address->region);
        $this->assertEquals('New Postal', $address->postalCode);
        $this->assertEquals('Mexico', $address->countryName);
        $this->assertEquals('MX', $address->countryCodeAlpha2);
        $this->assertEquals('MEX', $address->countryCodeAlpha3);
        $this->assertEquals('484', $address->countryCodeNumeric);
    }

    function testUpdate_withValidationErrors()
    {
        $customer = Customer::createNoValidate();
        $address = Address::createNoValidate(array(
            'customerId'    => $customer->id,
            'streetAddress' => '1 E Main St'
        ));
        $result = Address::update(
            $customer->id,
            $address->id,
            array(
                'countryName' => 'Invalid States of America'
            )
        );
        $this->assertFalse($result->success);
        $countryErrors = $result->errors->forKey('address')->onAttribute('countryName');
        $this->assertEquals(Codes::ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED, $countryErrors[0]->code);
    }

    function testUpdate_withValidationErrors_onCountry()
    {
        $customer = Customer::createNoValidate();
        $address = Address::createNoValidate(array(
            'customerId'    => $customer->id,
            'streetAddress' => '1 E Main St'
        ));
        $result = Address::update(
            $customer->id,
            $address->id,
            array(
                'countryCodeAlpha2' => 'MU',
                'countryCodeAlpha3' => 'MYT'
            )
        );
        $this->assertFalse($result->success);
        $countryErrors = $result->errors->forKey('address')->onAttribute('base');
        $this->assertEquals(Codes::ADDRESS_INCONSISTENT_COUNTRY, $countryErrors[0]->code);
    }


    function testUpdateNoValidate()
    {
        $customer = Customer::createNoValidate();
        $createdAddress = Address::createNoValidate(array(
            'customerId'      => $customer->id,
            'firstName'       => 'Old First',
            'lastName'        => 'Old Last',
            'company'         => 'Old Company',
            'streetAddress'   => '1 E Old St',
            'extendedAddress' => 'Apt Old',
            'locality'        => 'Old Chicago',
            'region'          => 'Old Region',
            'postalCode'      => 'Old Postal',
            'countryName'     => 'United States of America'
        ));
        $address = Address::updateNoValidate($customer->id, $createdAddress->id, array(
            'firstName'       => 'New First',
            'lastName'        => 'New Last',
            'company'         => 'New Company',
            'streetAddress'   => '1 E New St',
            'extendedAddress' => 'Apt New',
            'locality'        => 'New Chicago',
            'region'          => 'New Region',
            'postalCode'      => 'New Postal',
            'countryName'     => 'Mexico'
        ));
        $this->assertEquals('New First', $address->firstName);
        $this->assertEquals('New Last', $address->lastName);
        $this->assertEquals('New Company', $address->company);
        $this->assertEquals('1 E New St', $address->streetAddress);
        $this->assertEquals('Apt New', $address->extendedAddress);
        $this->assertEquals('New Chicago', $address->locality);
        $this->assertEquals('New Region', $address->region);
        $this->assertEquals('New Postal', $address->postalCode);
        $this->assertEquals('Mexico', $address->countryName);
    }
}
