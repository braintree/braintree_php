<?php
require_once __DIR__ . '/../TestHelper.php';

class Braintree_OAuthTest extends PHPUnit_Framework_TestCase
{
    /**
    * @expectedException Braintree_Exception_Configuration
    * @expectedExceptionMessage clientSecret needs to be set.
    */
    function testAssertsHasCredentials()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id'
        ));
        $gateway->oauth()->createTokenFromCode(array(
            'code' => 'integration_oauth_auth_code_' . rand(0,299)
        ));
    }

    function testCreateTokenFromCode()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $result = $gateway->oauth()->createTokenFromCode(array(
            'code' => 'integration_oauth_auth_code_' . rand(0,299),
            'scope' => 'read_write',
        ));

        $this->assertEquals(true, $result->success);
        $this->assertNotNull($result->accessToken);
        $this->assertNotNull($result->refreshToken);
        $this->assertEquals('bearer', $result->tokenType);
        $this->assertNotNull($result->expiresAt);
    }

    function testCreateTokenFromCodeFail()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $result = $gateway->oauth()->createTokenFromCode(array(
            'code' => 'bad_code',
            'scope' => 'read_write',
        ));

        $this->assertEquals(false, $result->success);
        $this->assertEquals('invalid_grant', $result->error);
        $this->assertEquals('code not found', $result->errorDescription);
    }

    function testCreateTokenFromRefreshToken()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $refreshToken = $gateway->oauth()->createTokenFromCode(array(
            'code' => 'integration_oauth_auth_code_' . rand(0,299),
            'scope' => 'read_write',
        ))->refreshToken;

        $result = $gateway->oauth()->createTokenFromRefreshToken(array(
            'refreshToken' => $refreshToken,
            'scope' => 'read_write',
        ));

        $this->assertEquals(true, $result->success);
        $this->assertNotNull($result->accessToken);
        $this->assertNotNull($result->refreshToken);
        $this->assertEquals('bearer', $result->tokenType);
        $this->assertNotNull($result->expiresAt);
    }


    function testBuildConnectUrl()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $url = $gateway->oauth()->connectUrl(array(
            'merchantId' => 'foo_merchant_id',
            'redirectUri' => 'http://bar.example.com',
            'scope' => 'read_write',
            'state' => 'baz_state',
            'user' => array(
                'country' => 'USA',
                'email' => 'foo@example.com',
                'firstName' => 'Bob',
                'lastName' => 'Jones',
                'phone' => '555-555-5555',
                'dobYear' => '1970',
                'dobMonth' => '01',
                'dobDay' => '01',
                'streetAddress' => '222 W Merchandise Mart',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60606',
                'website' => 'http://example.com',
            ),
            'business' => array(
                'name' => '14 Ladders',
                'registeredAs' => '14.0 Ladders',
                'industry' => 'Ladders',
                'description' => 'We sell the best ladders',
                'streetAddress' => '111 N Canal',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60606',
                'country' => 'USA',
                'annualVolumeAmount' => '1000000',
                'averageTransactionAmount' => '100',
                'maximumTransactionAmount' => '10000',
                'shipPhysicalGoods' => 'Yes',
                'fulfillmentCompletedIn' => '3 hours',
                'currency' => 'USD',
            ),
        ));

        $components = parse_url($url);
        $queryString = $components['query'];
        parse_str($queryString, $query);

        $this->assertEquals('localhost', $components['host']);
        $this->assertEquals('/oauth/connect', $components['path']);
        $this->assertEquals('foo_merchant_id', $query['merchant_id']);
        $this->assertEquals('client_id$development$integration_oauth_client_id', $query['client_id']);
        $this->assertEquals('http://bar.example.com', $query['redirect_uri']);
        $this->assertEquals('read_write', $query['scope']);
        $this->assertEquals('baz_state', $query['state']);

        $this->assertEquals('USA', $query['user']['country']);
        $this->assertEquals('foo@example.com', $query['user']['email']);
        $this->assertEquals('Bob', $query['user']['first_name']);
        $this->assertEquals('Jones', $query['user']['last_name']);
        $this->assertEquals('555-555-5555', $query['user']['phone']);
        $this->assertEquals('1970', $query['user']['dob_year']);
        $this->assertEquals('01', $query['user']['dob_month']);
        $this->assertEquals('01', $query['user']['dob_day']);
        $this->assertEquals('222 W Merchandise Mart', $query['user']['street_address']);
        $this->assertEquals('Chicago', $query['user']['locality']);
        $this->assertEquals('IL', $query['user']['region']);
        $this->assertEquals('60606', $query['user']['postal_code']);
        $this->assertEquals('http://example.com', $query['user']['website']);

        $this->assertEquals('14 Ladders', $query['business']['name']);
        $this->assertEquals('14.0 Ladders', $query['business']['registered_as']);
        $this->assertEquals('Ladders', $query['business']['industry']);
        $this->assertEquals('We sell the best ladders', $query['business']['description']);
        $this->assertEquals('111 N Canal', $query['business']['street_address']);
        $this->assertEquals('Chicago', $query['business']['locality']);
        $this->assertEquals('IL', $query['business']['region']);
        $this->assertEquals('60606', $query['business']['postal_code']);
        $this->assertEquals('USA', $query['business']['country']);
        $this->assertEquals('1000000', $query['business']['annual_volume_amount']);
        $this->assertEquals('100', $query['business']['average_transaction_amount']);
        $this->assertEquals('10000', $query['business']['maximum_transaction_amount']);
        $this->assertEquals('Yes', $query['business']['ship_physical_goods']);
        $this->assertEquals('3 hours', $query['business']['fulfillment_completed_in']);
        $this->assertEquals('USD', $query['business']['currency']);
    }

    function testBuildConnectUrlWithoutOptionalParams()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_oauth_client_id',
            'clientSecret' => 'client_secret$development$integration_oauth_client_secret'
        ));
        $url = $gateway->oauth()->connectUrl();

        $queryString = parse_url($url)['query'];
        parse_str($queryString, $query);

        $this->assertEquals('client_id$development$integration_oauth_client_id', $query['client_id']);
        $this->assertArrayNotHasKey('merchant_id', $query);
        $this->assertArrayNotHasKey('redirect_uri', $query);
        $this->assertArrayNotHasKey('scope', $query);
    }
}
